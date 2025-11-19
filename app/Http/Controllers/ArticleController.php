<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Categorie;
use App\Models\CategoriePrincipale;
use App\Models\NaturePrestation;
use App\Models\ArticleImage;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule; // AJOUTEZ CET IMPORT

class ArticleController extends Controller implements HasMiddleware
{


    public static function middleware(): array
    {
        return [
            // new Middleware('permission:list_articles', only: ['index']),
            // new Middleware('permission:show_articles', only: ['show']),
            // new Middleware('permission:create_articles', only: ['store']),
            // new Middleware('permission:edit_articles', only: ['edit', 'update']),
            // new Middleware('permission:delete_articles', only: ['destroy']),

            // new Middleware('permission:edit_admins', only: ['edit', 'update', 'activate', 'deactivate']),
        ];
    }

    /**
     * Afficher la page principale avec toutes les données
     */
    public function index(Request $request)
    {
        $search = $request->search;

        $articles = Article::with([
            'categorie:nom,id',
        ])
            ->when($search, function ($query, $search) {
                $query->where('reference', 'like', '%' . $search . '%')
                    ->orWhere('designation', 'like', '%' . $search . '%')
                    ->orWhereHas('categorie', function ($q) use ($search) {
                        $q->where('nom', 'like', '%' . $search . '%');
                    });
            })
            ->withNonExists()
            ->latest()
            ->paginate(10);

        return Inertia::render('Articles/Index', [
            'articles' => $articles,
            'filters' => $request->only(['search']),
        ]);
    }

    public function create()
    {
        return Inertia::modal('Articles/CreateArticleModal', [
            'categories' => Categorie::all(['id', 'nom']),
        ])->baseRoute('articles.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'reference' => 'required|string|max:50|unique:articles,reference',
            'designation' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'categorie_id' => 'required|exists:categories,id',
            'unite_mesure' => [
                'required',
                'string',
                'max:20',
                Rule::in(['kg', 'L', 'pièce', 'sachet', 'sac', 'boite', 'bidon', 'paquet', 'flacon', 'pot', 'bouteille']),
            ],
            'seuil_maximal' => 'required|integer|min:0',
            'est_actif' => 'boolean',
        ]);

        $nature = NaturePrestation::first();
        $categoryPrincipal = CategoriePrincipale::first();

        $article = Article::create([
            'reference' => $request->reference,
            'designation' => $request->designation,
            'description' => $request->description,
            'categorie_id' => $request->categorie_id,
            'categorie_principale_id' => $categoryPrincipal->id,
            'nature_prestation_id' => $nature->id,
            'unite_mesure' => $request->unite_mesure,
            'seuil_minimal' => -1,
            'seuil_maximal' => $request->seuil_maximal,
            'est_actif' => $request->boolean('est_actif'),
        ]);

        return redirect()->back()->with('success', 'Article créé avec succès.');
    }

    public function edit(Article $article)
    {
        $article->load(['categorie:id,nom']);

        return Inertia::modal('Articles/EditArticleModal', [
            'article' => $article,
            'categories' => Categorie::all(['id', 'nom']),
        ])->baseRoute('articles.index');
    }

    public function update(Request $request, Article $article)
    {
        $request->validate([
            'reference' => 'required|max:50|unique:articles,reference,' . $article->id,
            'designation' => 'required|max:255',
            'description' => 'nullable|string|max:255',
            'categorie_id' => 'required|exists:categories,id',
            'unite_mesure' => [
                'required',
                'string',
                'max:20',
                Rule::in(['kg', 'L', 'pièce', 'sachet', 'sac', 'boite', 'bidon', 'paquet', 'flacon', 'pot', 'bouteille']),
            ],
            'seuil_maximal' => 'required|integer|min:0',
            'est_actif' => 'boolean',
        ]);

        $article->update([
            'reference' => $request->reference,
            'designation' => $request->designation,
            'description' => $request->description,
            'categorie_id' => $request->categorie_id,
            'unite_mesure' => $request->unite_mesure,
            'seuil_maximal' => $request->seuil_maximal,
            'est_actif' => $request->boolean('est_actif'),
        ]);


        return redirect()->back()
            ->with('success', 'Article modifié avec succès.');
    }

    public function show(Article $article)
    {
        $article->load(['categorie', 'categoriePrincipale', 'naturePrestation', 'images']);

        return Inertia::render('Articles/ShowArticle', [
            'article' => $article,
        ]);
    }
}
