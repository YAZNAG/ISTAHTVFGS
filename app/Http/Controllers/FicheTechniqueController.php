<?php

namespace App\Http\Controllers;

use App\Enums\FicheType;
use App\Http\Requests\CreateFicheTechniqueRequest;
use App\Http\Requests\UpdateFicheTechniqueRequest;
use App\Http\Resources\EditFicheTechniqueResource;
use App\Http\Resources\IndexCollectiviteResource;
use App\Http\Resources\ShowFicheTechniqueResource;
use App\Models\Article;
use App\Models\BonCommandeArticle;
use App\Models\Etape;
use App\Models\FicheTechnique;
use App\Models\MouvementStock;
use App\Models\Repas;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Spatie\LaravelPdf\Facades\Pdf;

class FicheTechniqueController extends Controller implements HasMiddleware
{

    public static function middleware(): array
    {
        return [
            new Middleware('permission:list_ficheTechniques', only: ['pedagogique', 'collectivite']),
            new Middleware('permission:show_ficheTechniques', only: ['show']),
            new Middleware('permission:create_ficheTechniques', only: ['create', 'store']),
            new Middleware('permission:edit_ficheTechniques', only: ['edit', 'update']),
            new Middleware('permission:delete_ficheTechniques', only: ['destroy']),
            new Middleware('permission:pdf_ficheTechniques', only: ['export']),
        ];
    }
    
    public function pedagogique(Request $request)
    {
        $search = $request->search;
        $user = $request->user();

        $fiches = FicheTechnique::pedagogique()
        ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nom', 'like', "%{$search}%")
                    ->orWhere('plat', 'like', "%{$search}%")
                    ->orWhere('responsable', 'like', "%{$search}%");
                });
            })
        ->when(!$user->isAdmin(), fn($query) => $query->where('created_by', $user->id))
        ->paginate(10)
        ->withQueryString();


        return Inertia::render('Fiches/PedagogiqueIndex', [
            'fiches' => $fiches,
            'filters' => request()->all('search', 'trashed'),
        ]);
    }

    public function collectivite(Request $request)
    {
        $search = $request->search;
        $user = $request->user();

        $fiches = FicheTechnique::collectivite()
        ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nom', 'like', "%{$search}%")
                    ->orWhere('plat', 'like', "%{$search}%")
                    ->orWhere('responsable', 'like', "%{$search}%");
                });
            })
        ->when(!$user->isAdmin(), fn($query) => $query->where('created_by', $user->id))
        ->paginate(10)
        ->withQueryString();

        return Inertia::render('Fiches/CollectiveIndex', [
            'fiches' => IndexCollectiviteResource::collection($fiches),
            'filters' => request()->all('search', 'trashed'),
        ]);
    }

    public function create()
    {
        // $articles = Article::actives()->get(['id', 'designation', 'unite_mesure']);

        $articles = Article::actives()
            ->with('currentBonCommandeArticle')
            ->get(['id', 'designation', 'unite_mesure']);


        $articles = $articles->map(function ($article) {
            return [
                'id' => $article->id,
                'designation' => $article->designation,
                'unite_mesure' => $article->unite_mesure,
                'prix_unitaire' => $article->currentBonCommandeArticle->prix_unitaire_ht ?? 'Prix indisponible'
            ];
        });

        $repas = Repas::with('plats:id,nom,repas_id')->get();

        $data = [
            'articles' => $articles,
            'repas' => $repas,
            'types' => array_filter(
                        FicheType::toSelect(),
                        fn ($item) => $item['value'] !== FicheType::RESTAURANT->value
            ),
        ];


        if (auth()->user()->isAdmin()) {
            $data['demandeurs'] = User::permission('create_ficheTechniques')
                        ->withoutRole('manager')
                        ->get(['id', 'name']);
        }

        return Inertia::modal('Fiches/CreateFicheModal', $data)->baseUrl(url()->previous());
    }

    public function store(UpdateFicheTechniqueRequest $request)
    {

        DB::transaction(function () use ($request) {

            $created_by = auth()->user()->isAdmin() ? $request->demandeur : auth()->user()->id;
            ## Create fiche technique
            $fiche = FicheTechnique::create([
                'type' => $request->type,
                'responsable' => $request->responsable,
                'repas_id' => $request->repas_id,
                'plat_id' => $request->plat_id,
                'effectif' => $request->effectif,
                'created_by' => $created_by ?? auth()->user()->id
            ]);

            # create fiche technique etape
            $fiche->etapes()->createMany($request->etapes);

            # Create etape ingredients
            foreach ($request->articles as $ingredient) {
                $articleFromLastEntree = BonCommandeArticle::where('article_id', $ingredient['article_id'])
                    ->whereHas('bonCommande', function ($query) {
                        $query->whereDate('date_debut', '<=', now())
                            ->whereDate('date_fin', '>=', now());
                    })->first();
                
                $fiche->ingredients()->create([
                    'article_id' => $ingredient['article_id'],
                    'quantite' => $ingredient['quantite'],
                    'prix_unitaire' => $articleFromLastEntree->prix_unitaire_ht,
                    'taux_tva' => $articleFromLastEntree->taux_tva,
                ]);
            }
            
        });
        
    }

    public function show(FicheTechnique $fiche)
    {
        $fiche->load(['etapes', 'ingredients', 'ingredients.article', 'user']);

        return Inertia::modal('Fiches/ShowFicheModal', [
            'fiche' => ShowFicheTechniqueResource::make($fiche)
        ])->baseUrl(url()->previous());
    }

    public function edit(FicheTechnique $fiche)
    {
        $fiche->load(['etapes', 'ingredients', 'ingredients.article.currentBonCommandeArticle', 'user']);

        $articles = Article::actives()
            ->with('currentBonCommandeArticle')
            ->get(['id', 'designation', 'unite_mesure']);


        $articles = $articles->map(function ($article) {
            return [
                'id' => $article->id,
                'designation' => $article->designation,
                'unite_mesure' => $article->unite_mesure,
                'prix_unitaire' => $article->currentBonCommandeArticle->prix_unitaire_ht ?? 'Prix indisponible'
            ];
        });

        $repas = Repas::with('plats:id,nom,repas_id')->get();

        $data = [
            'articles' => $articles,
            'repas' => $repas,
            'fiche' => EditFicheTechniqueResource::make($fiche),
            'types' => array_filter(
                        FicheType::toSelect(),
                        fn ($item) => $item['value'] !== FicheType::RESTAURANT->value
            ),
        ];


        if (auth()->user()->isAdmin()) {
            $data['demandeurs'] = User::permission('create_ficheTechniques')
                        ->withoutRole('manager')
                        ->get(['id', 'name']);
        }

        return Inertia::render('Fiches/EditFicheModal', $data);
        // ])->baseUrl(url()->previous());
    }

    public function update(UpdateFicheTechniqueRequest $request, FicheTechnique $fiche)
    {
        DB::transaction(function () use ($request, $fiche) {

            $updated_by = auth()->user()->isAdmin() ? $request->demandeur : auth()->user()->id;

            // Update fiche technique main fields
            $fiche->update([
                'type' => $request->type,
                'repas_id' => $request->repas_id,
                'responsable' => $request->responsable,
                'plat_id' => $request->plat_id,
                'effectif' => $request->effectif,
                'created_by' => $updated_by ?? auth()->user()->id,
            ]);

            $fiche->etapes()->delete();
            $fiche->ingredients()->delete();
            
            # create fiche technique etape
            $fiche->etapes()->createMany($request->etapes);

            # Create etape ingredients
            foreach ($request->articles as $ingredient) {
                $articleFromLastEntree = BonCommandeArticle::where('article_id', $ingredient['article_id'])
                    ->whereHas('bonCommande', function ($query) {
                        $query->whereDate('date_debut', '<=', now())
                            ->whereDate('date_fin', '>=', now());
                    })->first();
                
                $fiche->ingredients()->create([
                    'article_id' => $ingredient['article_id'],
                    'quantite' => $ingredient['quantite'],
                    'prix_unitaire' => $articleFromLastEntree->prix_unitaire_ht,
                    'taux_tva' => $articleFromLastEntree->taux_tva,
                ]);
            }
        });
    }

    public function duplicate(FicheTechnique $fiche)
    {
        $fiche->load(['etapes', 'ingredients', 'ingredients.article.currentBonCommandeArticle', 'user']);

        $articles = Article::actives()
            ->with('currentBonCommandeArticle')
            ->get(['id', 'designation', 'unite_mesure']);


        $articles = $articles->map(function ($article) {
            return [
                'id' => $article->id,
                'designation' => $article->designation,
                'unite_mesure' => $article->unite_mesure,
                'prix_unitaire' => $article->currentBonCommandeArticle->prix_unitaire_ht ?? 'Prix indisponible'
            ];
        });

        $repas = Repas::with('plats:id,nom,repas_id')->get();

        $data = [
            'articles' => $articles,
            'repas' => $repas,
            'fiche' => EditFicheTechniqueResource::make($fiche),
            'types' => array_filter(
                        FicheType::toSelect(),
                        fn ($item) => $item['value'] !== FicheType::RESTAURANT->value
            ),
        ];


        if (auth()->user()->isAdmin()) {
            $data['demandeurs'] = User::permission('create_ficheTechniques')
                        ->withoutRole('manager')
                        ->get(['id', 'name']);
        }

        return Inertia::modal('Fiches/DuplicateFicheModal', $data)->baseUrl(url()->previous());
    }

    public function destroy(FicheTechnique $fiche)
    {
        $fiche->delete();

        return redirect()->back();
    }


    public function export(FicheTechnique $fiche)
    {
        $totalTtc = $fiche->etapes->sum(function ($etape) {
            return $etape->ingredients->sum('total_ttc');
        });

        $template = $fiche->type == FicheType::PEDAGOGIQUE ? 'fiche-pedagogique' : 'fiche-collective';

        return Pdf::view('pdf.' . $template, [
            'fiche' => $fiche,
            'totalTtc' => $totalTtc,
            'total_effectif' => round($totalTtc / $fiche->effectif, 2)
        ])->download($template . '-' . $fiche->nom . '.pdf');
    }
}
