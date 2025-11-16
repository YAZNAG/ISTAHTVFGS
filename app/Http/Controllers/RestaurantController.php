<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRestaurantRequest;
use App\Http\Resources\EditFicheTechniqueResource;
use App\Http\Resources\EditRestaurantResource;
use App\Http\Resources\ShowRestaurantResource;
use App\Models\Article;
use App\Models\BonCommandeArticle;
use App\Models\FicheTechnique;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class RestaurantController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;

        $restaurants = Restaurant::query()
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nom', 'like', "%{$search}%")
                        ->orWhere('responsable', 'like', "%{$search}%");
                });
            })
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Restaurants/Index', [
            'restaurants' => $restaurants,
            'filters' => request()->all('search'),
        ]);
    }

    public function show(Restaurant $restaurant)
    {
        $restaurant->load(['items', 'items.article']);

        return Inertia::modal('Restaurants/ShowModal', [
            'restaurant' => ShowRestaurantResource::make($restaurant)
        ])->baseRoute('restaurants.index');
    }

    public function create()
    {
        $articles = Article::actives()->get(['id', 'designation', 'unite_mesure']);


        $articles = $articles->map(function ($article) {
            return [
                'id' => $article->id,
                'designation' => $article->designation,
                'unite_mesure' => $article->unite_mesure,
                'prix_unitaire' => $article->price ?? 'Prix indisponible'
            ];
        });


        $data = [
            'articles' => $articles,
        ];


        // if (auth()->user()->isAdmin()) {
        //     $data['demandeurs'] = User::where('role', 'DEMANDEUR')->get(['id', 'name']);
        // }

        return Inertia::modal('Restaurants/CreateModal', $data)->baseRoute('restaurants.index');
    }

    public function store(StoreRestaurantRequest $request)
    {

        DB::transaction(function () use ($request) {

            // $created_by = auth()->user()->isAdmin() ? $request->demandeur : auth()->user()->id;
            ## Create Reastaurant
            $restaurant = Restaurant::create([
                'nom' => $request->nom,
                'responsable' => $request->responsable ?? 'Unkown',
                'effectif' => $request->effectif,
                'created_by' => auth()->user()->id
            ]);

            foreach ($request->articles as $item) {
                $articleFromLastEntree = BonCommandeArticle::where('article_id', $item['article_id'])
                    ->whereHas('bonCommande', function ($query) {
                        $query->whereDate('date_debut', '<=', now())
                            ->whereDate('date_fin', '>=', now());
                    })->first();

                $restaurant->items()->create([
                    'article_id' => $item['article_id'],
                    'quantite' => $item['quantite'],
                    'prix_unitaire' => $articleFromLastEntree->prix_unitaire_ht,
                    'taux_tva' => $articleFromLastEntree->taux_tva,
                ]);
            }
        });

        return redirect()->back()->with('success', 'Restaurant créé avec succès.');
    }



    public function edit(Restaurant $restaurant)
    {
        $restaurant->load(['items', 'items.article']);

        $articles = Article::actives()->get(['id', 'designation', 'unite_mesure']);

        $data = [
            'articles' => $articles,
            'restaurant' => EditRestaurantResource::make($restaurant)
        ];


        // if (auth()->user()->isAdmin()) {
        //     $data['demandeurs'] = User::where('role', 'DEMANDEUR')->get(['id', 'name']);
        // }

        return Inertia::modal('Restaurants/EditModal', $data)->baseRoute('restaurants.index');
    }

    public function update(StoreRestaurantRequest $request, Restaurant $restaurant)
    {
        DB::transaction(function () use ($request, $restaurant) {

            // 1. Update the restaurant itself
            $restaurant->update([
                'nom'         => $request->nom,
                'responsable' => $request->responsable ?? 'Unknown',
                'effectif'    => $request->effectif,
            ]);

            // 2. Sync the items
            // 2-a. Build an array of the IDs we want to keep
            $keptIds = collect($request->articles)
                ->pluck('article_id')          // <-- assumes every article in the request has an 'id' field
                ->filter()             // remove null/empty values (new rows)
                ->toArray();

            // 2-b. Delete the rows that are no longer present
            $restaurant->items()
                ->whereNotIn('id', $keptIds)
                ->delete();

            // 2-c. Insert or update the remaining rows
            foreach ($request->articles as $item) {
                // Fetch the current price/TVA from the last valid BonCommande
                $articleFromLastEntree = BonCommandeArticle::where('article_id', $item['article_id'])
                    ->whereHas('bonCommande', function ($q) {
                        $q->whereDate('date_debut', '<=', now())
                            ->whereDate('date_fin', '>=', now());
                    })->first();

                // If we didn't find a price, skip or throw an exception
                if (! $articleFromLastEntree) {
                    continue; // or throw new \Exception("No active price found for article {$item['article_id']}");
                }

                $restaurant->items()->updateOrCreate(
                    ['id' => $item['id'] ?? null],   // match existing row by id (null => create)
                    [
                        'article_id'    => $item['article_id'],
                        'quantite'      => $item['quantite'],
                        'prix_unitaire' => $articleFromLastEntree->prix_unitaire_ht,
                        'taux_tva'      => $articleFromLastEntree->taux_tva,
                    ]
                );
            }
        });

        return redirect()
            ->back()
            ->with('success', 'Restaurant modifié avec succès.');
    }

    public function destroy(Restaurant $restaurant)
    {
        $restaurant->items()->delete();
        $restaurant->delete();

        return redirect()->back()->with('success', 'Restaurant supprimé avec succès.');
    }
    
}
