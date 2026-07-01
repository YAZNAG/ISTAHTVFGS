<?php

namespace App\Http\Controllers;

use App\Exports\ArticlesExport;
use App\Models\Article;
use App\Models\BonCommandeArticle;
use App\Models\BonReceptionArticle;
use App\Models\Categorie;
use App\Models\LigneEntreeStock;
use App\Models\LigneReception;
use App\Models\LigneSortieStock;
use App\Models\MouvementStock;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class ArticleController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:list_articles', only: ['index', 'exportExcel', 'exportPdf']),
            new Middleware('permission:show_articles', only: ['show']),
            new Middleware('permission:create_articles', only: ['create', 'store']),
            new Middleware('permission:edit_articles', only: ['edit', 'update', 'destroy']),
        ];
    }

    public function index(Request $request)
    {
        $articles = $this->filteredArticles($request)
            ->with([
                'categorie:id,nom,code,couleur',
                'images:id,article_id,image_path,est_principale',
            ])
            ->withCount([
                'bonCommandeArticles as marches_count',
                'mouvementsStock as mouvements_count',
                'lignesReception as receptions_count',
                'lignesSortieStock as sorties_count',
            ])
            ->latest()
            ->paginate(10)
            ->withQueryString()
            ->through(fn (Article $article) => $this->articleIndexPayload($article));

        return Inertia::render('Articles/Index', [
            'articles' => $articles,
            'categories' => Categorie::orderBy('nom')->get(['id', 'nom', 'code', 'couleur']),
            'filters' => $request->only(['search', 'categorie_id', 'status', 'stock']),
            'stats' => [
                'total' => Article::withNonExists()->count(),
                'active' => Article::withNonExists()->where('est_actif', true)->count(),
                'inactive' => Article::withNonExists()->where('est_actif', false)->count(),
                'lowStock' => Article::withNonExists()
                    ->where('quantite_stock', '>', 0)
                    ->whereColumn('quantite_stock', '<=', 'seuil_minimal')
                    ->count(),
                'rupture' => Article::withNonExists()->where('quantite_stock', '<=', 0)->count(),
            ],
        ]);
    }

    public function create()
    {
        return Inertia::modal('Articles/CreateArticleModal', [
            'categories' => Categorie::actives()->orderBy('nom')->get(['id', 'nom', 'code']),
            'unitOptions' => $this->unitOptions(),
        ])->baseRoute('articles.index');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'reference' => ['required', 'string', 'max:50', 'unique:articles,reference'],
            'designation' => ['required', 'string', 'max:255'],
            'categorie_id' => ['required', 'exists:categories,id'],
            'unite_mesure' => ['required', 'string', 'max:20'],
            'taux_tva' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'stock_initial' => ['nullable', 'numeric', 'min:0'],
            'seuil_minimal' => ['nullable', 'numeric', 'min:0'],
            'seuil_maximal' => ['nullable', 'numeric', 'min:0'],
            'est_actif' => ['boolean'],
        ]);

        $categorie = Categorie::findOrFail($validated['categorie_id']);

        Article::create([
            'reference' => trim($validated['reference']),
            'designation' => trim($validated['designation']),
            'description' => null,
            'categorie_id' => $categorie->id,
            'couleur_affichage' => $categorie->couleur,
            'unite_mesure' => $validated['unite_mesure'],
            'taux_tva' => $validated['taux_tva'] ?? 0,
            'quantite_stock' => $validated['stock_initial'] ?? 0,
            'seuil_minimal' => $validated['seuil_minimal'] ?? 0,
            'seuil_maximal' => $validated['seuil_maximal'] ?? 0,
            'est_actif' => $request->boolean('est_actif', true),
            'in_marche' => true,
        ]);

        return redirect()->back()->with('success', 'Article cree avec succes.');
    }

    public function edit(Article $article)
    {
        $article->load(['categorie:id,nom,code']);

        return Inertia::modal('Articles/EditArticleModal', [
            'article' => [
                'id' => $article->id,
                'reference' => $article->reference,
                'designation' => $article->designation,
                'categorie_id' => $article->categorie_id,
                'unite_mesure' => $article->unite_mesure,
                'taux_tva' => (float) ($article->taux_tva ?? 0),
                'seuil_minimal' => (float) ($article->seuil_minimal ?? 0),
                'seuil_maximal' => (float) ($article->seuil_maximal ?? 0),
                'est_actif' => (bool) $article->est_actif,
            ],
            'categories' => Categorie::actives()->orderBy('nom')->get(['id', 'nom', 'code']),
            'unitOptions' => $this->unitOptions($article->unite_mesure),
        ])->baseRoute('articles.index');
    }

    public function update(Request $request, Article $article)
    {
        $validated = $request->validate([
            'reference' => ['required', 'string', 'max:50', Rule::unique('articles', 'reference')->ignore($article->id)],
            'designation' => ['required', 'string', 'max:255'],
            'categorie_id' => ['required', 'exists:categories,id'],
            'unite_mesure' => ['required', 'string', 'max:20'],
            'taux_tva' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'seuil_minimal' => ['nullable', 'numeric', 'min:0'],
            'seuil_maximal' => ['nullable', 'numeric', 'min:0'],
            'est_actif' => ['boolean'],
        ]);

        $categorie = Categorie::findOrFail($validated['categorie_id']);

        $article->update([
            'reference' => trim($validated['reference']),
            'designation' => trim($validated['designation']),
            'categorie_id' => $categorie->id,
            'couleur_affichage' => $categorie->couleur,
            'unite_mesure' => $validated['unite_mesure'],
            'taux_tva' => $validated['taux_tva'] ?? 0,
            'seuil_minimal' => $validated['seuil_minimal'] ?? 0,
            'seuil_maximal' => $validated['seuil_maximal'] ?? 0,
            'est_actif' => $request->boolean('est_actif', true),
        ]);

        return redirect()->back()->with('success', 'Article modifie avec succes.');
    }

    public function show(Article $article)
    {
        $article->load(['categorie:id,nom,code,couleur']);

        return Inertia::render('Articles/ShowArticle', [
            'article' => $this->articleDetailPayload($article),
            'mouvements' => $this->recentMovements($article),
            'marches' => $this->recentMarkets($article),
            'receptions' => $this->recentReceptions($article),
            'sorties' => $this->recentSorties($article),
        ]);
    }

    public function destroy(Article $article)
    {
        if ($this->articleIsUsed($article)) {
            $article->update(['est_actif' => false]);

            return redirect()
                ->back()
                ->with('warning', 'Cet article est deja utilise. Il a ete desactive au lieu d etre supprime.');
        }

        foreach ($article->images as $image) {
            if ($image->image_path) {
                Storage::disk('public')->delete($image->image_path);
            }
            $image->delete();
        }

        $article->delete();

        return redirect()->route('articles.index')->with('success', 'Article supprime avec succes.');
    }

    public function exportExcel(Request $request)
    {
        return Excel::download(new ArticlesExport($request->only(['search', 'categorie_id', 'status', 'stock'])), 'articles.xlsx');
    }

    public function exportPdf(Request $request)
    {
        return Pdf::loadView('pdf.articles', [
            'articles' => $this->filteredArticles($request)
                ->with('categorie:id,nom,code')
                ->orderBy('designation')
                ->get(),
        ])
            ->download('articles.pdf');
    }

    private function filteredArticles(Request $request)
    {
        return Article::withNonExists()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search')->toString();

                $query->where(function ($query) use ($search) {
                    $query->where('reference', 'like', "%{$search}%")
                        ->orWhere('designation', 'like', "%{$search}%")
                        ->orWhereHas('categorie', fn ($q) => $q->where('nom', 'like', "%{$search}%"));
                });
            })
            ->when($request->filled('categorie_id'), fn ($query) => $query->where('categorie_id', $request->categorie_id))
            ->when($request->filled('status'), fn ($query) => $query->where('est_actif', $request->status === 'actif'))
            ->when($request->filled('stock'), function ($query) use ($request) {
                if ($request->stock === 'rupture') {
                    $query->where('quantite_stock', '<=', 0);
                }

                if ($request->stock === 'faible') {
                    $query->where('quantite_stock', '>', 0)
                        ->whereColumn('quantite_stock', '<=', 'seuil_minimal');
                }

                if ($request->stock === 'normal') {
                    $query->where(function ($query) {
                        $query->whereColumn('quantite_stock', '>', 'seuil_minimal')
                            ->orWhereNull('seuil_minimal')
                            ->orWhere('seuil_minimal', '<=', 0);
                    })->where('quantite_stock', '>', 0);
                }
            });
    }

    private function articleIndexPayload(Article $article): array
    {
        $image = $article->images->firstWhere('est_principale', true) ?? $article->images->first();

        return [
            'id' => $article->id,
            'reference' => $article->reference,
            'designation' => $article->designation,
            'categorie' => $article->categorie ? [
                'id' => $article->categorie->id,
                'nom' => $article->categorie->nom,
                'code' => $article->categorie->code,
            ] : null,
            'categorie_id' => $article->categorie_id,
            'unite_mesure' => $article->unite_mesure,
            'quantite_stock' => (float) ($article->quantite_stock ?? 0),
            'taux_tva' => (float) ($article->taux_tva ?? 0),
            'seuil_minimal' => (float) ($article->seuil_minimal ?? 0),
            'seuil_maximal' => (float) ($article->seuil_maximal ?? 0),
            'est_actif' => (bool) $article->est_actif,
            'stock_status' => $this->stockStatus($article),
            'is_used' => ((int) $article->marches_count + (int) $article->mouvements_count + (int) $article->receptions_count + (int) $article->sorties_count) > 0,
            'image_url' => $image ? asset('storage/'.$image->image_path) : null,
        ];
    }

    private function articleDetailPayload(Article $article): array
    {
        return [
            'id' => $article->id,
            'reference' => $article->reference,
            'designation' => $article->designation,
            'categorie' => $article->categorie ? [
                'id' => $article->categorie->id,
                'nom' => $article->categorie->nom,
                'code' => $article->categorie->code,
                'couleur' => $article->categorie->couleur,
            ] : null,
            'unite_mesure' => $article->unite_mesure,
            'quantite_stock' => (float) ($article->quantite_stock ?? 0),
            'taux_tva' => (float) ($article->taux_tva ?? 0),
            'seuil_minimal' => (float) ($article->seuil_minimal ?? 0),
            'seuil_maximal' => (float) ($article->seuil_maximal ?? 0),
            'est_actif' => (bool) $article->est_actif,
            'stock_status' => $this->stockStatus($article),
            'is_used' => $this->articleIsUsed($article),
            'created_at' => optional($article->created_at)->format('d/m/Y'),
            'updated_at' => optional($article->updated_at)->format('d/m/Y'),
        ];
    }

    private function stockStatus(Article $article): array
    {
        return Article::computeStockStatus(
            (float) ($article->quantite_stock ?? 0),
            (float) ($article->seuil_minimal ?? 0)
        );
    }

    private function recentMovements(Article $article)
    {
        return MouvementStock::query()
            ->where('article_id', $article->id)
            ->latest('date_mouvement')
            ->limit(8)
            ->get()
            ->map(fn (MouvementStock $movement) => [
                'id' => $movement->id,
                'type' => $movement->type_mouvement,
                'date' => optional($movement->date_mouvement)->format('d/m/Y'),
                'quantite' => (float) ($movement->quantite_entree ?: $movement->quantite_sortie),
                'stock_apres' => (float) ($movement->quantite_actuelle ?? 0),
                'motif' => $movement->motif,
            ]);
    }

    private function recentMarkets(Article $article)
    {
        return BonCommandeArticle::query()
            ->with('bonCommande.fournisseur:id,nom,raison_sociale')
            ->where('article_id', $article->id)
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn (BonCommandeArticle $line) => [
                'id' => $line->id,
                'reference' => $line->bonCommande?->reference,
                'fournisseur' => $line->bonCommande?->fournisseur?->nom_affichage,
                'prix_unitaire_ht' => (float) ($line->prix_unitaire_ht ?? 0),
                'taux_tva' => (float) ($line->taux_tva ?? 0),
                'quantite_maximale' => (float) ($line->quantite_maximale ?? 0),
            ]);
    }

    private function recentReceptions(Article $article)
    {
        return LigneReception::query()
            ->with('bonReception:id,numero,date_reception,bon_commande_id')
            ->where('article_id', $article->id)
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn (LigneReception $line) => [
                'id' => $line->id,
                'numero' => $line->bonReception?->numero,
                'date' => optional($line->bonReception?->date_reception)->format('d/m/Y'),
                'quantite' => (float) ($line->quantite_receptionnee ?? 0),
                'prix_unitaire' => (float) ($line->prix_unitaire ?? 0),
            ]);
    }

    private function recentSorties(Article $article)
    {
        return MouvementStock::sorties()
            ->where('article_id', $article->id)
            ->latest('date_mouvement')
            ->limit(5)
            ->get()
            ->map(fn (MouvementStock $movement) => [
                'id' => $movement->id,
                'date' => optional($movement->date_mouvement)->format('d/m/Y'),
                'quantite' => (float) ($movement->quantite_sortie ?? 0),
                'motif' => $movement->motif,
            ]);
    }

    private function articleIsUsed(Article $article): bool
    {
        return BonCommandeArticle::where('article_id', $article->id)->exists()
            || BonReceptionArticle::where('article_id', $article->id)->exists()
            || LigneReception::where('article_id', $article->id)->exists()
            || LigneEntreeStock::where('article_id', $article->id)->exists()
            || LigneSortieStock::where('article_id', $article->id)->exists()
            || MouvementStock::where('article_id', $article->id)->exists();
    }

    private function unitOptions(?string $currentUnit = null): array
    {
        $options = ['kg', 'L', 'piece', 'sachet', 'sac', 'boite', 'bidon', 'paquet', 'flacon', 'pot', 'bouteille'];

        if ($currentUnit && ! in_array($currentUnit, $options, true)) {
            array_unshift($options, $currentUnit);
        }

        return $options;
    }
}
