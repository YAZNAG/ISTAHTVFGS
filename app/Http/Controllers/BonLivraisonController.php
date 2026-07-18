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
use Barryvdh\DomPDF\Facade\Pdf;

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
        $bonLivraisons = BonLivraison::livree()->withCount('items')->with('fournisseur:id,nom')
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

        $pendingLivraisons = BonLivraison::pending()->withCount('items')->with('fournisseur:id,nom')
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
        $chefCommandeItems = ChefCommandeItem::where('chef_commande_id', $bonLivraison->chef_commande_id)->get();

        // Quantites deja livrees sur ce chef de commande, hors le bon de livraison en cours d'edition
        $dejaLivre = BonLivraison::where('chef_commande_id', $bonLivraison->chef_commande_id)
            ->where('id', '!=', $bonLivraison->id)
            ->with('items')
            ->get()
            ->flatMap(fn ($bl) => $bl->items)
            ->groupBy('article_id')
            ->map(fn ($items) => $items->sum('quantite'));

        $articles = Article::select(['id', 'designation', 'unite_mesure'])
            ->whereIn('id', $chefCommandeItems->pluck('article_id'))
            ->get()
            ->map(function ($article) use ($marche, $chefCommandeItems, $dejaLivre) {
                $marcheArticle = $marche->articles->where('article_id', $article->id)->first();
                $quantiteCommandee = (float) ($chefCommandeItems->firstWhere('article_id', $article->id)?->quantite_commandee ?? 0);
                $quantiteDejaLivree = (float) ($dejaLivre->get($article->id) ?? 0);
                $resteALivrer = max(0, $quantiteCommandee - $quantiteDejaLivree);

                return [
                    'id' => $article->id,
                    'designation' => $article->designation,
                    'unite_mesure' => $article->unite_mesure,
                    'prix_unitaire' => $marcheArticle?->prix_unitaire_ht,
                    'taux_tva' => $marcheArticle?->taux_tva,
                    'quantite_commandee' => $quantiteCommandee,
                    'quantite_deja_livree' => $quantiteDejaLivree,
                    'reste_a_livrer' => $resteALivrer,
                ];
            })
            ->filter(fn ($article) => $article['reste_a_livrer'] > 0 || $article['quantite_deja_livree'] > 0)
            ->values();

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

        $chefCommandeItems = ChefCommandeItem::where('chef_commande_id', $bonLivraison->chef_commande_id)->get();

        $dejaLivre = BonLivraison::where('chef_commande_id', $bonLivraison->chef_commande_id)
            ->where('id', '!=', $bonLivraison->id)
            ->with('items')
            ->get()
            ->flatMap(fn ($bl) => $bl->items)
            ->groupBy('article_id')
            ->map(fn ($items) => $items->sum('quantite'));

        foreach ($request->items as $item) {
            $quantiteCommandee = (float) ($chefCommandeItems->firstWhere('article_id', $item['article_id'])?->quantite_commandee ?? 0);
            $quantiteDejaLivree = (float) ($dejaLivre->get($item['article_id']) ?? 0);
            $resteALivrer = max(0, $quantiteCommandee - $quantiteDejaLivree);

            if ((float) $item['quantite'] > $resteALivrer) {
                return back()->withErrors([
                    'items' => 'La quantite saisie depasse le reste a livrer pour un des articles.',
                ])->withInput();
            }
        }

        DB::transaction(function () use ($request, $bonLivraison, $user_id, $chefCommandeItems, $dejaLivre) {

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

            $totalLivreApres = $chefCommandeItems->groupBy('article_id')->map(function ($items, $articleId) use ($request, $dejaLivre) {
                $apresCetteLivraison = collect($request->items)->where('article_id', $articleId)->sum('quantite');

                return ($dejaLivre->get($articleId) ?? 0) + $apresCetteLivraison;
            });

            $estCompletementLivre = $chefCommandeItems->every(function ($ccItem) use ($totalLivreApres) {
                return ($totalLivreApres->get($ccItem->article_id) ?? 0) >= (float) $ccItem->quantite_commandee;
            });

            $estPartiellementLivre = $totalLivreApres->sum() > 0;

            $bonLivraison->update([
                'date_livraison' => $request->date_livraison,
                'statut' => $estCompletementLivre
                    ? BonLivraison::STATUS_LIVREE
                    : ($estPartiellementLivre ? BonLivraison::STATUS_PARTIELLEMENT_LIVREE : BonLivraison::STATUS_EN_ATTENTE_LIVRAISON),
                'responsable_id' => $user_id,
            ]);

            $bonLivraison->chefCommande->update([
                'statut' => $estCompletementLivre ? ChefCommande::STATUS_LIVRE_COMPLETEMNT : ChefCommande::STATUS_LIVRE_PARTIELLEMENT,
            ]);
        });

        return redirect()->route('bon-livraisons.index')->with('success', 'Bon de livraison mis à jour avec succès.');
    }


    public function export(Request $request, BonLivraison $bonLivraison)
    {
        $bonLivraison->load(['items.article', 'fournisseur']);

        $bonLivraison = ExportBonLivraisonResource::make($bonLivraison)->toArray($request);

        return Pdf::loadView('pdf.bon-livraison', [
            'livraison'    => $bonLivraison,
            'pdfHeaderSrc' => $this->pdfHeaderBase64(),
        ])
        ->setPaper('a4', 'portrait')
        ->download("bon-livraison-{$bonLivraison['reference']}.pdf");
    }
}
