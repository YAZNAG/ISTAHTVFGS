<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReturnStockRequest;
use App\Http\Resources\ReturnIndexResource;
use App\Http\Resources\ReturnShowResource;
use App\Models\Article;
use App\Models\BonCommandeArticle;
use App\Models\MouvementStock;
use App\Models\ReturnStock;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ReturnStockController extends Controller implements HasMiddleware
{

    public static function middleware(): array
    {
        return [
            new Middleware('permission:returns_stocks', only: ['index', 'show', 'create', 'store', 'destroy']),

        ];
    }


    public function index(Request $request)
    {
        $search = $request->search;
        $query = ReturnStock::with(['returner:id,name', 'receiver:id,name'])
            ->latest()
            ->withCount('items as articles_count');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('numero', 'like', '%' . $search . '%')
                    ->orWhere('motif', 'like', '%' . $search . '%');

                $q->orWhereHas('returner', function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%');
                });

                $q->orWhereHas('receiver', function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%');
                });
            });
        }

        if ($request->filled('article_id')) {
            $query->whereHas('items', function ($q) use ($request) {
                $q->where('article_id', $request->article_id);
            });
        }

        if ($request->filled('date_debut')) {
            $query->whereDate('date', '>=', $request->date_debut);
        }

        if ($request->filled('date_fin')) {
            $query->whereDate('date', '<=', $request->date_fin);
        }

        $returns = $query->paginate(10)->withQueryString();
        return Inertia::render('Stock/ReturnStocks/Index', [
            'returns' => ReturnIndexResource::collection($returns),
            'filters' => request()->all('search', 'article_id', 'date_debut', 'date_fin'),
            'articles' => Article::actives()->orderBy('designation')->get(['id', 'reference', 'designation']),
        ]);
    }

    public function create()
    {
        $users = User::all(['id', 'name']);
        $articles = Article::all(['id', 'designation', 'unite_mesure']);

        return Inertia::render('Stock/ReturnStocks/CreateReturnModal', [
            'users' => $users,
            'articles' => $articles
        ]);
    }

    public function store(StoreReturnStockRequest $request)
    {
        DB::transaction(function () use ($request) {

            $returnStock = ReturnStock::create([
                'date' => $request->date,
                'returner_id' => $request->returner_id,
                'receiver_id' => auth()->user()->id,
                'motif' => $request->motif,
                'numero' => ReturnStock::generateNumero(),
            ]);

            foreach ($request->articles as $item) {
                $returnItem = $returnStock->items()->create([
                    'article_id' => $item['article_id'],
                    'quantite' => $item['quantite'],
                ]);

                $articleFromLastEntree = BonCommandeArticle::where('article_id', $returnItem->article_id)
                    ->whereHas('bonCommande', function ($query) {
                        $query->whereDate('date_debut', '<=', now())
                            ->whereDate('date_fin', '>=', now());
                    })->first();

                $returnItem->article->increment('quantite_stock', $returnItem->quantite);

                $nouvelleQuantiteActuelle = $returnItem->article->quantite_stock;

                MouvementStock::create([
                    'type' => MouvementStock::TYPE_ENTREE,
                    'article_id' => $returnItem->article_id,
                    'date_mouvement' => now(),
                    'prix_unitaire' => $articleFromLastEntree->prix_unitaire_ht,
                    'taux_tva' => $articleFromLastEntree->taux_tva,
                    'type_mouvement' => MouvementStock::TYPE_ENTREE,
                    'quantite_entree' => $returnItem->quantite,
                    'quantite_sortie' => 0,
                    'quantite_actuelle' => $nouvelleQuantiteActuelle,
                    'motif' => 'Retour de stock n° ' . $returnStock->numero,
                    'referenceable_id' => $returnStock->id,
                    'referenceable_type' => ReturnStock::class,
                ]);
            }
        });

        return redirect()->back()->with('success', 'Le retour de stock a été ajouté avec succès.');
    }

    public function show(ReturnStock $returnStock)
    {
        $returnStock->load(['returner:id,name', 'receiver:id,name', 'items.article:id,designation,unite_mesure']);

        return Inertia::modal('Stock/ReturnStocks/ShowReturnModal', ['returnStock' => new ReturnShowResource($returnStock)])
            ->baseRoute('returns.index');
    }

    public function destroy(ReturnStock $returnStock)
    {
        DB::transaction(function () use ($returnStock) {
            // 1.  Reverse the stock move
            foreach ($returnStock->items as $item) {
                $article = $item->article;
                $article->decrement('quantite_stock', $item->quantite);
            }

            // 2.  Delete the movement rows that were created by this return
            MouvementStock::where('referenceable_id', $returnStock->id)
                ->where('referenceable_type', ReturnStock::class)
                ->delete();

            // 3.  Delete the items then the header
            $returnStock->items()->delete();
            $returnStock->delete();
        });

        return redirect()->back()->with('success', 'Le retour de stock a été supprimé avec succès.');
    }
}
