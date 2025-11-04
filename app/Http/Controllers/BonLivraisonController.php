<?php

namespace App\Http\Controllers;

use App\Http\Resources\EditBonLivraisonResource;
use App\Http\Resources\IndexBonLivraisonResource;
use App\Models\Article;
use App\Models\BonCommande;
use App\Models\BonLivraison;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class BonLivraisonController extends Controller
{
    public function index()
    {
        $bonLivraisons = BonLivraison::livree()->withCount('items')->paginate(10);
        $pendingLivraisons = BonLivraison::pending()->withCount('items')->get();

        return Inertia::render('BonLivraisons/Index', [
            'bonLivraisons' => IndexBonLivraisonResource::collection($bonLivraisons),
            'pendingLivraisons' => IndexBonLivraisonResource::collection($pendingLivraisons)
        ]);
    }


    public function create()
    {
        // return Inertia::modal('BonLivraisons/Create');
    }

    public function edit(BonLivraison $bonLivraison)
    {

        $marche = BonCommande::find($bonLivraison->chefCommande->bon_commande_id)->load('articles');
        $articles = Article::select(['id', 'designation', 'unite_mesure'])->whereIn('id', $marche->articles->pluck('article_id'))->get();

        $articles = $articles->map(function ($article) use ($marche) {
            return [
                'id' => $article->id,
                'designation' => $article->designation,
                'unite_mesure' => $article->unite_mesure,
                'prix_unitaire' => $marche->articles->where('article_id', $article->id)->first()->prix_unitaire_ht,
                'taux_tva' => $marche->articles->where('article_id', $article->id)->first()->taux_tva
            ];
        });

        return Inertia::modal('BonLivraisons/Edit', [
            'bonLivraison' => EditBonLivraisonResource::make($bonLivraison),
            'articles' => $articles,
            'users' => User::all(['id', 'name'])
        ]);
    }

    public function update(Request $request, BonLivraison $bonLivraison)
    {
        $request->validate([
            'date_livraison' => 'required|date',
            'user_id' => 'required|exists:users,id',
            'items' => 'required|array',
            'items.*.article_id' => 'required|exists:articles,id',
            'items.*.quantite' => 'required|numeric',
        ]);

        DB::transaction(function () use ($request, $bonLivraison) {


            $bonLivraison->update([
                'date_livraison' => $request->date_livraison,
                'statut' => BonLivraison::STATUS_LIVREE,
                'user_id' => $request->user_id
            ]);

            $bonLivraison->items()->delete();
            $marche = BonCommande::find($bonLivraison->chefCommande->bon_commande_id)->load('articles');

            foreach ($request->items as $item) {
                $marcheArticle = $marche->articles->where('article_id', $item['article_id'])->first();
                $bonLivraison->items()->create([
                    'article_id' => $item['article_id'],
                    'quantite' => $item['quantite'],
                    'prix_unitaire' => $marcheArticle?->prix_unitaire_ht ?? -1,
                    'taux_tva' => $marcheArticle?->taux_tva ?? -1,
                ]);
            }
        });

        return redirect()->route('bon-livraisons.index')->with('success', 'Bon de livraison mis à jour avec succès.');
    }
}
