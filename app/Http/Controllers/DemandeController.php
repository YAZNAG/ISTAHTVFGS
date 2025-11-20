<?php

namespace App\Http\Controllers;

use App\Enums\DemandeStatut;
use App\Enums\FicheType;
use App\Http\Requests\StoreDemandeRequest;
use App\Http\Resources\EditDemendeResource;
use App\Http\Resources\FicheTechniqueDemandeResource;
use App\Http\Resources\MesDemendesResource;
use App\Http\Resources\RestaurantDemandeResource;
use App\Http\Resources\ShowDemendeResource;
use App\Models\Article;
use App\Models\BonCommandeArticle;
use App\Models\Demande;
use App\Models\FicheTechnique;
use App\Models\MouvementStock;
use App\Models\Restaurant;
use App\Models\SortieStock;
use App\Models\User;
use App\Rules\InStockRule;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class DemandeController extends Controller implements HasMiddleware
{


    public static function middleware(): array
    {
        return [
            new Middleware('permission:list_demandes', only: ['index']),
            new Middleware('permission:show_demandes', only: ['show']),
            new Middleware('permission:create_demandes', only: ['create', 'store']),
            new Middleware('permission:validate_demandes', only: ['showApprove', 'approve', 'reject']),

        ];
    }
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('list', Demande::class);
        
        $user = auth()->user();

        $search = $request->get('search');
        $status = $request->get('status');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        
        $demandes = Demande::query()
            ->with(['valideur'])
            ->withCount('articles')
            ->when(!$user->isAdmin(), function ($query) use ($user) {
                $query->where('demandeur_id', $user->id);
            })
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('numero', 'like', "%{$search}%")
                    ->orWhere('objet', 'like', "%{$search}%");
                });
            })
            ->when($status, fn($query) => $query->where('statut', $status))
            ->when($startDate, fn($query) => $query->whereDate('created_at', '>=', $startDate))
            ->when($endDate, fn($query) => $query->whereDate('created_at', '<=', $endDate))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Demandes/Index', [
            'demandes' => MesDemendesResource::collection($demandes),
            'filters' => [
                'search' => $search,
                'status' => $status,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
        ]);
    }

    public function create(Request $request) {
        $this->authorize('create', Demande::class);

        
        $fichesCollectives = FicheTechnique::collectivite()->with('ingredients')->get();

        $fichesPedagogiques = FicheTechnique::pedagogique()->with('ingredients')->get();

        $restaurants = Restaurant::with(['items', 'items.article'])->get();
        
        $data = [
            'fichesCollectives' => FicheTechniqueDemandeResource::collection($fichesCollectives),
            'fichesPedagogiques' => FicheTechniqueDemandeResource::collection($fichesPedagogiques),
            'restaurants' => RestaurantDemandeResource::collection($restaurants),

            'types' => FicheType::toSelect(),
        ];


        if (auth()->user()->isAdmin()) {
            $data['demandeurs'] = User::where('role', 'DEMANDEUR')->get(['id', 'name']);
        }

        return Inertia::render('Demandes/CreateDemandeModal', $data);
    }
    
    public function store(StoreDemandeRequest $request) {
        $this->authorize('create', Demande::class);

        
        // $request->validate([
        //     'demandeur' => ['nullable', Rule::requiredIf(fn () => auth()->user()->isAdmin()), 'integer', 'exists:users,id'],
        //     'fiche_technique' => 'required|mimes:pdf,doc,docx,png,jpg,jpeg|max:5120',
        //     'motif' => 'nullable|string|max:500',
        //     'fiche_id' => 'required|exists:fiches_techniques,id',
        // ]);

        DB::transaction(function () use ($request) {
            
            $user_id = auth()->user()->isAdmin() ? $request->demandeur : auth()->user()->id;

            $model = $request->demandable_type == FicheType::RESTAURANT->value ? Restaurant::class : FicheTechnique::class;
            $demandable = $model::findOrFail($request->demandable_id);

            $demande = Demande::create([
                'numero' => Demande::generateNumero(),
                'demandeur_id' => $user_id,
                'motif' => $request->input('motif'),
                'statut' => DemandeStatut::CREE,
                'demandable_id'   => $demandable->id,
                'demandable_type' => $demandable->getMorphClass(),
                'type' => $request->demandable_type,
            ]);


            
            if ($demandable instanceof FicheTechnique) {
                // existing behaviour
                foreach ($demandable->ingredients as $ing) {
                    $demande->articles()->create([
                        'article_id'        => $ing['article_id'],
                        'quantite_demandee' => $ing['quantite'],
                    ]);
                }

                $demande->addMediaFromRequest('fiche_technique')->toMediaCollection('fiches_techniques');
            }

            if ($demandable instanceof Restaurant) {
                foreach ($demandable->items as $item) {
                    $demande->articles()->create([
                        'article_id'        => $item->article_id, 
                        'quantite_demandee' => $item->quantite, 
                    ]);
                }
            }

            
        });

        return redirect()->back();
    }

    public function submit(Demande $demande)
    {
        if ($demande->statut != DemandeStatut::CREE->value && $demande->statut != DemandeStatut::EN_ATTENTE_VALIDATION->value) {
            return redirect()->back()->with('error', 'Impossible de modifier ce demande.');
        }
        
        $demande->update([
            'statut' => DemandeStatut::EN_ATTENTE_VALIDATION,
        ]);

        return redirect()->back()->with('success', 'Demande mise à jour avec succès.');
    }

    public function edit(Demande $demande) {
        $this->authorize('update', $demande);
        
        $articles = Article::all(['id', 'designation']);
        $demande->load(['articles']);

        $fichesCollectives = FicheTechnique::collectivite()->with('ingredients')->get();

        $fichesPedagogiques = FicheTechnique::pedagogique()->with('ingredients')->get();
        
        $data = [
            'articles' => $articles,
            'demande' => EditDemendeResource::make($demande),
            'fichesCollectives' => FicheTechniqueDemandeResource::collection($fichesCollectives),
            'fichesPedagogiques' => FicheTechniqueDemandeResource::collection($fichesPedagogiques)
        ];

        if (auth()->user()->isAdmin()) {
            $data['demandeurs'] = User::where('role', 'DEMANDEUR')->get(['id', 'name']);
        }


        return Inertia::modal('Demandes/EditDemandeModal', $data)->baseRoute('demandes.index');
    }

    public function update(Request $request, Demande $demande) {
        
        $this->authorize('update', $demande);
        
        $request->validate([
            'demandeur' => ['nullable', Rule::requiredIf(fn () => auth()->user()->isAdmin()), 'integer', 'exists:users,id'],
            'fiche_technique' => 'nullable|mimes:pdf,doc,docx,png,jpg,jpeg|max:5120',
            'fiche_id' => 'required|exists:fiches_techniques,id',
            'motif' => 'nullable|string|max:500',
        ]);

        
        DB::transaction(function () use ($request, $demande) {
            $fiche = FicheTechnique::findOrFail($request->fiche_id);
            
            $user_id = auth()->user()->isAdmin() ? $request->demandeur : auth()->user()->id;
            $demande->update([
                'motif' => $request->input('motif'),
                'user_id' => $user_id,
                'fiche_id' => $request->fiche_id
            ]);
            
            $demande->articles()->delete();

                // Add new/updated articles
            foreach ($fiche->ingredients as $article) {
                $demande->articles()->create([
                    'article_id' => $article['article_id'],
                    'quantite_demandee' => $article['quantite'],
                ]);
            }

            if ($request->hasFile('fiche_technique')) {
                $demande->addMediaFromRequest('fiche_technique')->toMediaCollection('fiches_techniques');
            }
            
        });

        return redirect()->back();
    }

    public function show(Demande $demande) {
        $this->authorize('show', $demande);
        
        $demande->load(['articles', 'valideur']);

        return Inertia::modal('Demandes/ShowDemandeModal', [
            'demande' => ShowDemendeResource::make($demande)
        ])->baseRoute('demandes.index');
    }

    public function cancel(Demande $demande) {
        if ($demande->statut != DemandeStatut::CREE->value && $demande->statut != DemandeStatut::EN_ATTENTE_VALIDATION->value) {
            return redirect()->back()->with('error', 'Impossible de modifier ce demande.');
        }

        $demande->update(['statut' => DemandeStatut::ANNULEE]);
        return redirect()->back();
    }

    public function showApprove(Demande $demande) {
        $this->authorize('approve', $demande);

        return Inertia::modal('Demandes/ApproveModal', [
            'demande' => ShowDemendeResource::make($demande)
        ])->baseRoute('demandes.index');
    } 

    public function approve(Request $request, Demande $demande) {
        if ($demande->statut != DemandeStatut::EN_ATTENTE_VALIDATION->value) {
            return redirect()->back()->with('error', 'Impossible de modifier ce demande. ' . $demande->statut);
        }

        $request->validate([
            'commentaire_validation' => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($demande, $request) {
            
            $demande->update([
                'statut' => DemandeStatut::VALIDEE,
                'commentaire_validation' => $request->input('commentaire_validation'),
                'date_validation' => now(),
                'valide_par' => auth()->user()->id
            ]);

            $sortieStock = SortieStock::create([
                'numero' => SortieStock::genererNumero(),
                'type_sortie' => SortieStock::TYPE_DEMANDE,
                'demandeur_id' => $demande->demandeur_id,
                'date_sortie' => now(),
                'demande_id' => $demande->id,
                'motif' => "Cette sortie est générée automatiquement à partir de la demande n° {$demande->numero}",
                'statut' => SortieStock::STATUT_ATTENTE_VALIDATION,
            ]);
            
            foreach ($demande->articles as $articleLine) {

                # Article line from the last marche
                // $lastEntreeArticle = MouvementStock::entrees()->where('article_id', $articleLine->article_id)
                //                     ->orderBy('date_mouvement', 'desc')
                //                     ->orderBy('id', 'desc')
                //                     ->first();

                $lastEntreeArticle = BonCommandeArticle::where('article_id', $articleLine->article_id)
                ->whereHas('bonCommande', function ($query) {
                    $query->whereDate('date_debut', '<=', now())
                        ->whereDate('date_fin', '>=', now());
                })->first();
                
                $sortieStock->lignesSortie()->create([
                    'article_id' => $articleLine->article_id,
                    'quantite' => $articleLine->quantite_demandee,
                    'prix_unitaire' => $lastEntreeArticle->prix_unitaire_ht,
                    'taux_tva' => $lastEntreeArticle->taux_tva
                ]);
            }
            
        });
        
        return redirect()->back();
    } 


    public function reject(Request $request, Demande $demande) {
        if ($demande->statut != DemandeStatut::EN_ATTENTE_VALIDATION->value) {
            return redirect()->back()->with('error', 'Impossible de modifier ce demande.');
        }

         $request->validate([
            'commentaire_validation' => 'nullable|string|max:500',
        ]);

        $demande->update([
            'statut' => DemandeStatut::REJETEE,
            'commentaire_validation' => $request->input('commentaire_validation'),
            'date_validation' => now(),
        ]);

        
        return redirect()->back();
    } 

}
