<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreChefCommandeRequest;
use App\Http\Resources\ChefCommandeResource;
use App\Http\Resources\EditChefCommandeResource;
use App\Http\Resources\ShowChefCommandeResource;
use App\Models\Article;
use App\Models\BonCommande;
use App\Models\BonLivraison;
use App\Models\Categorie;
use App\Models\ChefCommande;
use App\Models\ChefCommandeItem;
use App\Models\User;
use App\Notifications\ChefCommandeApproved;
use App\Notifications\ChefCommandeRejected;
use App\Notifications\NewChefCommadeCreated;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

class ChefCommandeController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:list_chefCommandes', only: ['index']),
            new Middleware('permission:show_chefCommandes', only: ['show']),
            new Middleware('permission:create_chefCommandes', only: ['create', 'store']),
            new Middleware('permission:edit_chefCommandes', only: ['update', 'edit']),
            new Middleware('permission:validate_chefCommandes', only: ['showApprove', 'approve', 'reject']),
            new Middleware('permission:pdf_chefCommandes', only: ['generatePdf']),

        ];
    }
    public function index(Request $request)
    {
        $user = $request->user();
        $search = $request->get('search');
        $status = $request->get('status');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $canListAll = $user->hasPermissionTo('validate_chefCommandes');

        $chefCommandes = ChefCommande::withCount('articles')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('numero', 'like', "%{$search}%")
                    ;
                });
            })
            ->when(!$canListAll, fn($query) => $query->where('user_id', $user->id))
            ->when($status, fn($query) => $query->where('statut', $status))
            ->when($startDate, fn($query) => $query->whereDate('created_at', '>=', $startDate))
            ->when($endDate, fn($query) => $query->whereDate('created_at', '<=', $endDate))
            ->paginate(10)
            ->withQueryString();
        
        
        return Inertia::render('ChefCommande/Index', [
            'chefCommandes' => ChefCommandeResource::collection($chefCommandes),
            'filters' => [
                'search' => $search,
                'status' => $status,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
        ]);
    }


    public function create()
    {
        $users = User::permission('create_chefCommandes')
                    ->withoutRole('manager')
                    ->get(['id', 'name']);

        return Inertia::modal('ChefCommande/CreateCommandeModal', [
            'articles' => Article::where('est_actif', true)->orderBy('designation')->get(['id', 'designation', 'categorie_id', 'unite_mesure']),
            'categories' => $this->categoryOptions(),
            'users' => $users,
        ])->baseRoute('chef-commandes.index');
    }

    private function categoryOptions()
    {
        return Categorie::query()
            ->where('est_actif', true)
            ->orderBy('nom')
            ->get(['id', 'nom', 'code']);
    }

    private function chefCommandeFieldsForCategory(array $payload, Categorie $categorie): array
    {
        $payload['categorie_id'] = $categorie->id;
        return $payload;
    }

    private function currentMarketForCategory(int $categorieId): BonCommande
    {
        $marche = BonCommande::current()
            ->where('categorie_id', $categorieId)
            ->whereNotNull('fournisseur_id')
            ->whereIn('statut', [BonCommande::STATUT_ATTENTE_LIVRAISON, BonCommande::STATUT_LIVRE_PARTIELLEMENT])
            ->with('articles')
            ->latest()
            ->first();

        if (!$marche) {
            throw ValidationException::withMessages([
                'categorie_id' => 'Aucun marche actif attribue pour cette categorie.',
            ]);
        }

        return $marche;
    }


    public function store(StoreChefCommandeRequest $request)
    {
        $user_id = $request->user()->isAdmin() ? $request->user_id : $request->user()->id;
        
        $categorie = Categorie::findOrFail($request->categorie_id);
        $marche = $this->currentMarketForCategory($categorie->id);
        
        $missingArticles = collect($request->articles)->pluck('article_id')->diff(
            $marche->articles->pluck('article_id')
        );

        if (!$missingArticles->isEmpty()) {
            $articles = Article::whereIn('id', $missingArticles)->pluck('designation');
            throw ValidationException::withMessages([
                'articlesError' => "les articles suivants sont pas dans le marché actuel: " . "<strong>" . $articles->join(', ') . "<strong>",
            ]);
        }
        
        
        #FIX: it creates multiple chef commandes
        $chefCommande = ChefCommande::create($this->chefCommandeFieldsForCategory([
            'numero' => ChefCommande::genererNumero(),
            'note' => $request->note,
            'statut' => $request->type == 'submit' ? ChefCommande::STATUS_EN_ATTENTE_VALIDATION : ChefCommande::STATUS_CREE,
            'user_id' => $user_id,
        ], $categorie));



        foreach ($request->articles as $article) {
            ChefCommandeItem::create([
                'chef_commande_id' => $chefCommande->id,
                'article_id' => $article['article_id'],
                'quantite_commandee' => $article['quantite_commandee'],
            ]);

        }
        
        if ($request->type == 'submit') {
            $usersToNotify = User::permission('validate_chefCommandes')->get(['id', 'email', 'name']);

            foreach ($usersToNotify as $user) {
                $user->notify(new NewChefCommadeCreated($chefCommande));
            }
        }

        return redirect()->back()->with('success', 'Commande crée avec succès.');
    }

    public function show(ChefCommande $chefCommande)
    {
        $chefCommande->load('articles');
        return Inertia::modal('ChefCommande/ShowCommandeModal', [
            'chefCommande' => ShowChefCommandeResource::make($chefCommande)
        ])
        ->baseRoute('chef-commandes.index');
        ;
    }

    public function edit(ChefCommande $chefCommande)
    {
        $articles = Article::where('est_actif', true)->orderBy('designation')->get(['id', 'designation', 'categorie_id', 'unite_mesure']);
        $users = User::permission('create_chefCommandes')
                    ->withoutRole('manager')
                    ->get(['id', 'name']);

        return Inertia::modal('ChefCommande/EditCommandeModal', [
            'chefCommande' => EditChefCommandeResource::make($chefCommande),
            'articles' => $articles,
            'categories' => $this->categoryOptions(),
            'users' => $users,
        ])
        ->baseRoute('chef-commandes.index');
        ;
    }

    public function update(StoreChefCommandeRequest $request, ChefCommande $chefCommande)
    {
        if ($chefCommande->statut !== ChefCommande::STATUS_CREE && $chefCommande->statut !== ChefCommande::STATUS_EN_ATTENTE_VALIDATION) {
            return redirect()->back()
                ->with('error', 'Impossible de modifier ce bon de commande.');
        }

        $categorie = Categorie::findOrFail($request->categorie_id);
        $marche = $this->currentMarketForCategory($categorie->id);
        
        $missingArticles = collect($request->articles)->pluck('article_id')->diff(
            $marche->articles->pluck('article_id')
        );

        if (!$missingArticles->isEmpty()) {
            $articles = Article::whereIn('id', $missingArticles)->pluck('designation');
            throw ValidationException::withMessages([
                'articlesError' => "les articles suivants sont pas dans le marché actuel: " . "<strong>" . $articles->join(', ') . "<strong>",
            ]);
        }

        $user_id = $request->user()->isAdmin() ? $request->user_id : $request->user()->id;

        $chefCommande->update($this->chefCommandeFieldsForCategory([
            'note' => $request->note,
            'user_id' => $user_id,
        ], $categorie));

        $chefCommande->items()->delete();

        foreach ($request->articles as $article) {
            ChefCommandeItem::create([
                'chef_commande_id' => $chefCommande->id,
                'article_id' => $article['article_id'],
                'quantite_commandee' => $article['quantite_commandee'],
            ]);

        }

        return redirect()->back()->with('success', 'Commande mise à jour avec succès.');
    }

    public function submit(ChefCommande $chefCommande)
    {

        if ($chefCommande->statut !== ChefCommande::STATUS_CREE) {
            return redirect()->back()
                ->with('error', 'Impossible de modifier ce bon de commande.');
        }

        $chefCommande->update([
            'statut' => ChefCommande::STATUS_EN_ATTENTE_VALIDATION,
        ]);

        $usersToNotify = User::permission('validate_chefCommandes')->get();

        foreach ($usersToNotify as $user) {
            $user->notify(new NewChefCommadeCreated($chefCommande));
        }

        return redirect()->back()->with('success', 'Commande mise à jour avec succès.');
    }

    public function cancel(ChefCommande $chefCommande)
    {
        $cancellable = [
            ChefCommande::STATUS_CREE,
            ChefCommande::STATUS_EN_ATTENTE_VALIDATION,
            ChefCommande::STATUS_EN_ATTENTE_LIVRAISON,
        ];

        if (! in_array($chefCommande->statut, $cancellable, true)) {
            return redirect()->back()
                ->with('error', 'Impossible d\'annuler ce bon de commande : il est deja livre ou annule.');
        }

        // Bloquer uniquement si une livraison a reellement ete effectuee ou receptionnee
        // (le BL cree automatiquement a l'approbation reste en "en_attente_livraison")
        $livraisonEffectuee = $chefCommande->livraisons()
            ->where(function ($q) {
                $q->where('statut', '!=', BonLivraison::STATUS_EN_ATTENTE_LIVRAISON)
                  ->orWhereHas('reception');
            })
            ->exists();

        if ($livraisonEffectuee) {
            return redirect()->back()
                ->with('error', 'Impossible d\'annuler : une livraison a deja ete effectuee ou receptionnee pour ce bon.');
        }

        DB::transaction(function () use ($chefCommande) {
            // Supprimer les BL en attente (crees automatiquement a l'approbation)
            foreach ($chefCommande->livraisons()->get() as $bl) {
                $bl->items()->delete();
                $bl->delete();
            }
            $chefCommande->update([
                'statut' => ChefCommande::STATUS_ANNULEE,
            ]);
        });

        return redirect()->back()->with('success', 'Bon de commande annule avec succes.');
    }

    public function destroy(ChefCommande $chefCommande)
    {
        $deletable = [
            ChefCommande::STATUS_CREE,
            ChefCommande::STATUS_EN_ATTENTE_LIVRAISON,
            ChefCommande::STATUS_ANNULEE,
            ChefCommande::STATUS_REJET,
        ];

        if (! in_array($chefCommande->statut, $deletable, true)) {
            return redirect()->back()
                ->with('error', 'Impossible de supprimer ce bon de commande : il est deja livre.');
        }

        $livraisonEffectuee = $chefCommande->livraisons()
            ->where(function ($q) {
                $q->where('statut', '!=', BonLivraison::STATUS_EN_ATTENTE_LIVRAISON)
                  ->orWhereHas('reception');
            })
            ->exists();

        if ($livraisonEffectuee) {
            return redirect()->back()
                ->with('error', 'Impossible de supprimer : une livraison a deja ete effectuee ou receptionnee pour ce bon.');
        }

        DB::transaction(function () use ($chefCommande) {
            foreach ($chefCommande->livraisons()->get() as $bl) {
                $bl->items()->delete();
                $bl->delete();
            }
            $chefCommande->items()->delete();
            $chefCommande->delete();
        });

        return redirect()->route('chef-commandes.index')
            ->with('success', 'Bon de commande supprime avec succes.');
    }


    public function showApprove(ChefCommande $chefCommande) {
        // $this->authorize('approve', $demande);
        $marches = BonCommande::select(['id', 'reference'])
            ->current()
            ->whereNotNull('fournisseur_id')
            ->whereIn('statut', [BonCommande::STATUT_ATTENTE_LIVRAISON, BonCommande::STATUT_LIVRE_PARTIELLEMENT])
            ->where('categorie_id', $chefCommande->categorie_id)->get();

        return Inertia::modal('ChefCommande/ApproveModal', [
            'chefCommande' => ShowChefCommandeResource::make($chefCommande),
            'marches' => $marches
        ])->baseRoute('chef-commandes.index');
    } 

    public function approve(Request $request, ChefCommande $chefCommande) {
        // $this->authorize('approve', $demande);

        if ($chefCommande->statut !== ChefCommande::STATUS_EN_ATTENTE_VALIDATION) {
            return redirect()->back()
                ->with('error', 'Impossible de modifier ce bon de commande.');
        }

        $request->validate([
            'validation_note' => 'nullable|string|max:500',
            // 'marche_id' => 'required|integer|exists:bon_commandes,id',
        ]);
        
        $marche = $this->currentMarketForCategory($chefCommande->categorie_id);
        
        $missingArticles = $chefCommande->items()->pluck('article_id')->diff(
            $marche->articles->pluck('article_id')
        );

        if (!$missingArticles->isEmpty()) {
            $articles = Article::whereIn('id', $missingArticles)->pluck('designation');
            throw ValidationException::withMessages([
                'articlesError' => "les articles suivants sont pas dans le marché actuel: " . "<strong>" . $articles->join(', ') . "<strong>",
            ]);
        }
        
        DB::transaction(function () use ($chefCommande, $request, $marche) {

            
            $chefCommande->update([
            'statut' => ChefCommande::STATUS_EN_ATTENTE_LIVRAISON,
            'validation_note' => $request->input('validation_note'),
            'bon_commande_id' => $marche->id,
            'validation_date' => now(),
        ]);


        $bonlivraison = BonLivraison::create([
            'numero' => BonLivraison::genererNumero(),
            'statut' => BonLivraison::STATUS_EN_ATTENTE_LIVRAISON,
            'chef_commande_id' => $chefCommande->id,
            'fournisseur_id' => $marche->fournisseur_id,
        ]);

        ### Verify if all articles are belongs to the same marche

        

        foreach ($chefCommande->items as $item) {
            $marcheArticle = $marche->articles->where('article_id', $item->article_id)->first();
            $bonlivraison->items()->create([
                'quantite' => $item->quantite_commandee,
                'prix_unitaire' => $marcheArticle?->prix_unitaire_ht ?? -1,
                'taux_tva' => $marcheArticle?->taux_tva ?? -1,
                'article_id' => $item->article_id,
            ]);
        }
            
    });
          
        $chefCommande->user->notify(new ChefCommandeApproved($chefCommande));

        return redirect()->back()->with('success', 'Commande mise à jour avec succès.');
    } 


    public function reject(Request $request, ChefCommande $chefCommande) {
        // $this->authorize('approve', $chefCommande);
        if ($chefCommande->statut !== ChefCommande::STATUS_EN_ATTENTE_VALIDATION) {
            return redirect()->back()
                ->with('error', 'Impossible de modifier ce bon de commande.');
        }

         $request->validate([
            'validation_note' => 'nullable|string|max:500',
        ]);

        $chefCommande->update([
            'statut' => ChefCommande::STATUS_REJET,
            'validation_note' => $request->input('validation_note'),
            'validation_date' => now(),
        ]);

        $chefCommande->user->notify(new ChefCommandeRejected($chefCommande));

        return redirect()->back()->with('success', 'Commande mise à jour avec succès.');
    } 

    public function generatePdf(ChefCommande $chefCommande)
    {
        $notAllowedStatus = [
            ChefCommande::STATUS_CREE,
            ChefCommande::STATUS_ANNULEE,
            ChefCommande::STATUS_REJET,
        ];

        if (in_array($chefCommande->statut, $notAllowedStatus)) {
            abort(403, 'PDF non disponible pour ce statut');
        }

        $chefCommande->load([
            'items.article',
            'items.article.currentBonCommandeArticle',
        ]);

        $data = [
            'chefCommande' => $chefCommande,
        ];

        
        $cleanReference = preg_replace('/[\/\\\\]/', '-', $chefCommande->reference);
        $fileName = "chef-commande-{$cleanReference}.pdf";
        
        return Pdf::loadView('pdf.chef-commande.chef-commande', $data)
            ->download($fileName);
    }
}
