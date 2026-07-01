<?php

namespace App\Http\Controllers;

use App\Exports\CategoriesExport;
use App\Models\Article;
use App\Models\BonCommande;
use App\Models\Categorie;
use App\Models\ChefCommande;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class CategorieController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:list_categories', only: ['index', 'show', 'exportExcel', 'exportPdf']),
            new Middleware('permission:create_categories', only: ['store']),
            new Middleware('permission:edit_categories', only: ['edit', 'update']),
            new Middleware('permission:delete_categories', only: ['destroy']),
        ];
    }

    public function index(Request $request)
    {
        $categories = Categorie::query()
            ->withCount([
                'articles as articles_count' => fn ($query) => $query->withNonExists(),
            ])
            ->orderBy('nom')
            ->get()
            ->map(fn (Categorie $categorie) => $this->categoryCardPayload($categorie));

        return Inertia::render('Categories/Index', [
            'categories' => $categories,
            'stats' => [
                'total' => $categories->count(),
                'actives' => $categories->where('est_actif', true)->count(),
                'inactives' => $categories->where('est_actif', false)->count(),
                'articles' => $categories->sum('articles_count'),
            ],
            'filters' => [
                'search' => $request->string('search')->toString(),
                'status' => $request->string('status', 'all')->toString(),
            ],
        ]);
    }

    public function show(Categorie $categorie)
    {
        $categorie->loadCount([
            'articles as articles_count' => fn ($query) => $query->withNonExists(),
        ]);

        $articles = Article::withNonExists()
            ->where('categorie_id', $categorie->id)
            ->orderBy('designation')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (Article $article) => [
                'id' => $article->id,
                'reference' => $article->reference,
                'designation' => $article->designation,
                'unite_mesure' => $article->unite_mesure,
                'quantite_stock' => (float) ($article->quantite_stock ?? 0),
                'seuil_minimal' => (float) ($article->seuil_minimal ?? 0),
                'seuil_maximal' => (float) ($article->seuil_maximal ?? 0),
                'statut' => $this->articleStatus($article),
            ]);

        return Inertia::render('Categories/Show', [
            'categorie' => [
                'id' => $categorie->id,
                'code' => $categorie->code,
                'nom' => $categorie->nom,
                'couleur' => $categorie->couleur ?: '#155e9f',
                'est_actif' => (bool) $categorie->est_actif,
                'articles_count' => (int) $categorie->articles_count,
                'created_at' => optional($categorie->created_at)->format('d/m/Y'),
                'updated_at' => optional($categorie->updated_at)->format('d/m/Y'),
            ],
            'articles' => $articles,
        ]);
    }

    public function store(Request $request)
    {
        Categorie::create($this->validatedPayload($request));

        return redirect()
            ->route('categories.index')
            ->with('success', 'Categorie creee avec succes.');
    }

    public function edit(Categorie $categorie)
    {
        return Inertia::modal('Categories/EditCategorieModal', [
            'categorie' => [
                'id' => $categorie->id,
                'code' => $categorie->code,
                'nom' => $categorie->nom,
                'couleur' => $categorie->couleur ?: '#155e9f',
                'est_actif' => (bool) $categorie->est_actif,
            ],
        ])->baseRoute('categories.index');
    }

    public function update(Request $request, Categorie $categorie)
    {
        $categorie->update($this->validatedPayload($request, $categorie->id));

        return redirect()
            ->back()
            ->with('success', 'Categorie mise a jour avec succes.');
    }

    public function destroy(Categorie $categorie)
    {
        $articlesCount = Article::withNonExists()
            ->where('categorie_id', $categorie->id)
            ->count();

        $hasBusinessDocuments = BonCommande::query()
            ->where('categorie_id', $categorie->id)
            ->exists()
            || ChefCommande::query()
                ->where('categorie_id', $categorie->id)
                ->exists();

        if ($articlesCount > 0 || $hasBusinessDocuments) {
            $categorie->update(['est_actif' => false]);

            return redirect()
                ->back()
                ->with('warning', 'Cette categorie contient des donnees liees. Elle a ete desactivee au lieu d etre supprimee.');
        }

        $categorie->delete();

        return redirect()
            ->back()
            ->with('success', 'Categorie supprimee avec succes.');
    }

    public function exportExcel()
    {
        return Excel::download(new CategoriesExport, 'categories.xlsx');
    }

    public function exportPdf()
    {
        return Pdf::loadView('pdf.categories', [
            'categories' => $this->exportRows(),
        ])
            ->download('categories.pdf');
    }

    private function validatedPayload(Request $request, ?int $categorieId = null): array
    {
        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('categories', 'code')->ignore($categorieId),
            ],
            'nom' => ['required', 'string', 'max:100'],
            'couleur' => ['nullable', 'string', 'max:20'],
            'est_actif' => ['boolean'],
        ]);

        return [
            'code' => trim($validated['code']),
            'nom' => trim($validated['nom']),
            'couleur' => ($validated['couleur'] ?? null) ?: '#155e9f',
            'est_actif' => $request->boolean('est_actif', true),
        ];
    }

    private function categoryCardPayload(Categorie $categorie): array
    {
        return [
            'id' => $categorie->id,
            'code' => $categorie->code,
            'nom' => $categorie->nom,
            'couleur' => $categorie->couleur ?: '#155e9f',
            'est_actif' => (bool) $categorie->est_actif,
            'articles_count' => (int) $categorie->articles_count,
            'created_at' => optional($categorie->created_at)->format('d/m/Y'),
        ];
    }

    private function articleStatus(Article $article): array
    {
        return Article::computeStockStatus(
            (float) ($article->quantite_stock ?? 0),
            (float) ($article->seuil_minimal ?? 0)
        );
    }

    private function exportRows()
    {
        return Categorie::query()
            ->withCount([
                'articles as articles_count' => fn ($query) => $query->withNonExists(),
            ])
            ->orderBy('nom')
            ->get();
    }
}
