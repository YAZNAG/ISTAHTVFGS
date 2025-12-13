<?php

namespace App\Http\Controllers;

use App\Http\Resources\EditBonLivraisonResource;
use App\Http\Resources\ExportBonLivraisonResource;
use App\Http\Resources\IndexBonLivraisonResource;
use App\Http\Resources\ShowBonLivraisonResource;
use App\Models\Article;
use App\Models\BonCommande;
use App\Models\BonLivraison;
use App\Models\ChefCommande;
use App\Models\ChefCommandeItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Spatie\LaravelPdf\Enums\Format;
use Spatie\LaravelPdf\Facades\Pdf;

class BonLivraisonController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:list_bonLivraisons', only: ['index']),
            new Middleware('permission:show_bonLivraisons', only: ['show']),
            new Middleware('permission:validate_bonLivraisons', only: ['edit', 'update']),
            new Middleware('permission:pdf_bonLivraisons', only: ['export']),

        ];
    }
    public function index(Request $request)
    {
        $search = $request->search;
        $responsable_id = $request->responsable_id;
        $bonLivraisons = BonLivraison::livree()->withCount('items')
            ->when($search, function ($query, $search) {
                return $query->where('numero', 'like', '%' . $search . '%')
                    ->orWhereHas('fournisseur', function ($query) use ($search) {
                        $query->where('nom', 'like', '%' . $search . '%');
                    });
            })
            ->when($responsable_id, function ($query, $responsable_id) {
                return $query->where('responsable_id', $responsable_id);
            })
            ->paginate(10)->withQueryString();

        $pendingLivraisons = BonLivraison::pending()->withCount('items')
            ->when($search, function ($query, $search) {
                return $query->where('numero', 'like', '%' . $search . '%')
                    ->orWhereHas('fournisseur', function ($query) use ($search) {
                        $query->where('nom', 'like', '%' . $search . '%');
                    });
            })
            ->when($responsable_id, function ($query, $responsable_id) {
                return $query->where('responsable_id', $responsable_id);
            })
        ->get();

        $magasiniers = User::permission('validate_bonLivraisons')
                    ->withoutRole('manager')
                    ->get(['id', 'name']);

        return Inertia::render('BonLivraisons/Index', [
            'bonLivraisons' => IndexBonLivraisonResource::collection($bonLivraisons),
            'pendingLivraisons' => IndexBonLivraisonResource::collection($pendingLivraisons),
            'magasiniers' => $magasiniers,
            'filtres' => [
                'search' => $search,
                'responsable_id' => $responsable_id
            ]
        ]);
    }


    public function create()
    {
        // return Inertia::modal('BonLivraisons/Create');
    }

    public function show(BonLivraison $bonLivraison)
    {
        $bonLivraison->load(['items', 'responsable', 'fournisseur']);

        return Inertia::render('BonLivraisons/ShowDetails', [
            'bonLivraison' => ShowBonLivraisonResource::make($bonLivraison)
        ]);
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

        $users = User::permission('validate_bonLivraisons')
                    ->withoutRole('manager')
                    ->get(['id', 'name']);

        return Inertia::modal('BonLivraisons/Edit', [
            'bonLivraison' => EditBonLivraisonResource::make($bonLivraison),
            'articles' => $articles,
            'users' => $users,
        ]);
    }

    public function update(Request $request, BonLivraison $bonLivraison)
    {
        $request->validate([
            'date_livraison' => 'required|date',
            'user_id' => ['nullable', Rule::requiredIf(fn () => auth()->user()->isAdmin()), 'integer', 'exists:users,id'],
            'items' => 'required|array',
            'items.*.article_id' => 'required|exists:articles,id',
            'items.*.quantite' => 'required|numeric',
        ]);

        $user_id = $request->user()->isAdmin() ? $request->user_id : $request->user()->id;

        DB::transaction(function () use ($request, $bonLivraison, $user_id) {


            $bonLivraison->update([
                'date_livraison' => $request->date_livraison,
                'statut' => BonLivraison::STATUS_LIVREE,
                'responsable_id' => $user_id
            ]);

            $chefCommandArticles = ChefCommandeItem::where('chef_commande_id', $bonLivraison->chef_commande_id)->pluck('article_id');
            $newChefCommandStatus = $chefCommandArticles->diff(collect($request->items)->pluck('article_id'))->isEmpty() ? ChefCommande::STATUS_LIVRE_COMPLETEMNT : ChefCommande::STATUS_LIVRE_PARTIELLEMENT;

            $bonLivraison->chefCommande->update([
                'statut' => $newChefCommandStatus
            ]);
            
            $marche = BonCommande::find($bonLivraison->chefCommande->bon_commande_id)->load('articles');
            
            $bonLivraison->items()->delete();

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


    public function export(Request $request, BonLivraison $bonLivraison)
    {
        $bonLivraison->load(['items.article', 'fournisseur']);

        $bonLivraison = ExportBonLivraisonResource::make($bonLivraison)->toArray($request);

        // return response()->json($bonLivraison);
        return Pdf::view('pdf.bon-livraison', [
            'livraison' => $bonLivraison
        ])->format(Format::A4)
            ->margins(5, 5, 5, 5)
            ->download("bon-livraison-{$bonLivraison['reference']}.pdf");
    }
}
