<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Categorie;
use App\Models\CategoriePrincipale;
use App\Models\NaturePrestation;
use App\Models\ArticleImage;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule; // AJOUTEZ CET IMPORT

class ArticleController extends Controller
{
    /**
     * Afficher la page principale avec toutes les données
     */
    public function index()
    {
        $articles = Article::with([
                'categorie', 
                'categoriePrincipale', 
                'naturePrestation', 
                'images',
                'lastEntryStock',
            ])
            ->latest()
            ->paginate(10);

        $categories = Categorie::with([
            'categoriePrincipale', 
            'naturePrestation'
        ])->get();

        return Inertia::render('Articles/Index', [
            'articles' => $articles,
            'categoriesPrincipales' => CategoriePrincipale::all(),
            'naturesPrestation' => NaturePrestation::all(),
            'categories' => $categories,
        ]);
    }

    /**
     * Méthode store() requise par Laravel Resource Controller
     */
    public function store(Request $request)
    {
        return $this->storeArticle($request);
    }

    // ==================== ARTICLES ====================

    /**
     * Stocker un nouvel article
     */
    public function storeArticle(Request $request)
    {
        $request->validate([
            'reference' => 'required|string|max:50|unique:articles,reference',
            'designation' => 'required|string|max:255',
            'description' => 'nullable|string',
            'categorie_id' => 'required|exists:categories,id',
            'unite_mesure' => [
                'required','string','max:20',
                Rule::in(['kg','L','pièce','sachet','sac','boite','bidon','paquet','flacon','pot','bouteille']),
            ],
            'seuil_maximal' => 'required|integer|min:0', // CORRECTION: ajout de gte
            'est_actif' => 'boolean',
            'images' => 'nullable|array',
            'images.*' => 'file|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        try {
            DB::transaction(function () use ($request) {
                // Créer l'article
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

                // Upload images si présentes
                if ($request->hasFile('images')) {
                    foreach ($request->file('images') as $image) {
                        $path = $image->store('articles/images', 'public');

                        $article->images()->create([
                            'image_path' => $path,
                            'nom_fichier' => $image->getClientOriginalName(),
                            'est_principale' => false,
                        ]);
                    }
                }
            });

            return redirect()->route('articles.index')
                ->with('success', 'Article créé avec succès.');

        } catch (\Throwable $e) {
            return back()->with('error', "Erreur lors de la création de l'article : ".$e->getMessage())
                         ->withInput();
        }
    }

    /**
     * Mettre à jour un article
     */
    public function update(Request $request, Article $article)
    {
        return $this->updateArticle($request, $article);
    }

    public function updateArticle(Request $request, Article $article)
    {
        $request->validate([
            'reference' => 'required|max:50|unique:articles,reference,' . $article->id,
            'designation' => 'required|max:255',
            'description' => 'nullable',
            'categorie_id' => 'required|exists:categories,id',
            'categorie_principale_id' => 'required|exists:categorie_principales,id',
            'nature_prestation_id' => 'required|exists:nature_prestations,id',
            'unite_mesure' => [
                'required','string','max:20',
                Rule::in(['kg','L','pièce','sachet','sac','boite','bidon','paquet','flacon','pot','bouteille']),
            ],
            'seuil_minimal' => 'required|integer|min:0',
            'seuil_maximal' => 'required|integer|min:0|gte:seuil_minimal', // CORRECTION: ajout de gte
            'est_actif' => 'boolean',
            'images' => 'nullable|array',
            'images.*' => 'file|image|mimes:jpg,jpeg,png,webp|max:4096',
        ]);

        try {
            DB::transaction(function () use ($request, $article) {
                $article->update([
                    'reference' => $request->reference,
                    'designation' => $request->designation,
                    'description' => $request->description,
                    'categorie_id' => $request->categorie_id,
                    'categorie_principale_id' => $request->categorie_principale_id,
                    'nature_prestation_id' => $request->nature_prestation_id,
                    'unite_mesure' => $request->unite_mesure,
                    'seuil_minimal' => $request->seuil_minimal,
                    'seuil_maximal' => $request->seuil_maximal,
                    'est_actif' => $request->boolean('est_actif'),
                ]);

                // Upload de nouvelles images si présentes
                if ($request->hasFile('images')) {
                    foreach ($request->file('images') as $image) {
                        $path = $image->store('articles/images', 'public');

                        $article->images()->create([
                            'image_path' => $path,
                            'nom_fichier' => $image->getClientOriginalName(),
                            'est_principale' => false,
                        ]);
                    }
                }
            });

            return redirect()->route('articles.index')
                ->with('success', 'Article modifié avec succès.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de la modification de l\'article: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Supprimer un article
     */
    public function destroy(Article $article)
    {
        return $this->destroyArticle($article);
    }

    public function destroyArticle(Article $article)
    {
        try {
            DB::transaction(function () use ($article) {
                // Supprimer les images associées
                foreach ($article->images as $image) {
                    Storage::disk('public')->delete($image->image_path);
                    $image->delete();
                }

                $article->delete();
            });

            return redirect()->route('articles.index')
                ->with('success', 'Article supprimé avec succès.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de la suppression de l\'article: ' . $e->getMessage());
        }
    }

    // ==================== CATÉGORIES ====================

    /**
     * Stocker une nouvelle catégorie
     */
    public function storeCategorie(Request $request)
    {
        $request->validate([
            'nom' => 'required|max:100',
            'code' => 'required|max:20|unique:categories,code',
            'description' => 'nullable',
            'categorie_principale_id' => 'required|exists:categorie_principales,id',
            'nature_prestation_id' => 'required|exists:nature_prestations,id',
            'est_actif' => 'boolean',
        ]);

        try {
            Categorie::create($request->all());

            return redirect()->route('articles.index')
                ->with('success', 'Catégorie créée avec succès.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de la création de la catégorie: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Mettre à jour une catégorie
     */
    public function updateCategorie(Request $request, Categorie $categorie)
    {
        $request->validate([
            'nom' => 'required|max:100',
            'code' => 'required|max:20|unique:categories,code,' . $categorie->id,
            'description' => 'nullable',
            'categorie_principale_id' => 'required|exists:categorie_principales,id',
            'nature_prestation_id' => 'required|exists:nature_prestations,id',
            'est_actif' => 'boolean',
        ]);

        try {
            $categorie->update($request->all());

            return redirect()->route('articles.index')
                ->with('success', 'Catégorie modifiée avec succès.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de la modification de la catégorie: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Supprimer une catégorie
     */
    public function destroyCategorie(Categorie $categorie)
    {
        try {
            // Vérifier si la catégorie est utilisée par des articles
            if ($categorie->articles()->count() > 0) {
                return redirect()->back()
                    ->with('error', 'Impossible de supprimer cette catégorie car elle est utilisée par des articles.');
            }

            $categorie->delete();

            return redirect()->route('articles.index')
                ->with('success', 'Catégorie supprimée avec succès.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de la suppression de la catégorie: ' . $e->getMessage());
        }
    }

    // ==================== CATÉGORIES PRINCIPALES ====================

    /**
     * Stocker une nouvelle catégorie principale
     */
    public function storeCategoriePrincipale(Request $request)
    {
        $request->validate([
            'nom' => 'required|max:100',
            'code' => 'required|max:20|unique:categorie_principales,code',
            'description' => 'nullable',
            'est_actif' => 'boolean',
        ]);

        try {
            CategoriePrincipale::create($request->all());

            return redirect()->route('articles.index')
                ->with('success', 'Catégorie principale créée avec succès.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de la création de la catégorie principale: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Mettre à jour une catégorie principale
     */
    public function updateCategoriePrincipale(Request $request, CategoriePrincipale $categoriePrincipale)
    {
        $request->validate([
            'nom' => 'required|max:100',
            'code' => 'required|max:20|unique:categorie_principales,code,' . $categoriePrincipale->id,
            'description' => 'nullable',
            'est_actif' => 'boolean',
        ]);

        try {
            $categoriePrincipale->update($request->all());

            return redirect()->route('articles.index')
                ->with('success', 'Catégorie principale modifiée avec succès.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de la modification de la catégorie principale: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Supprimer une catégorie principale
     */
    public function destroyCategoriePrincipale(CategoriePrincipale $categoriePrincipale)
    {
        try {
            // Vérifier si la catégorie principale est utilisée
            if ($categoriePrincipale->categories()->count() > 0 || $categoriePrincipale->articles()->count() > 0) {
                return redirect()->back()
                    ->with('error', 'Impossible de supprimer cette catégorie principale car elle est utilisée.');
            }

            $categoriePrincipale->delete();

            return redirect()->route('articles.index')
                ->with('success', 'Catégorie principale supprimée avec succès.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de la suppression de la catégorie principale: ' . $e->getMessage());
        }
    }

    // ==================== NATURES DE PRESTATION ====================

    /**
     * Stocker une nouvelle nature de prestation
     */
    public function storeNaturePrestation(Request $request)
    {
        $request->validate([
            'nom' => 'required|max:100',
            'code' => 'required|max:20|unique:nature_prestations,code',
            'description' => 'nullable',
            'categorie_principale_id' => 'required|exists:categorie_principales,id',
            'est_actif' => 'boolean',
        ]);

        try {
            NaturePrestation::create($request->all());

            return redirect()->route('articles.index')
                ->with('success', 'Nature de prestation créée avec succès.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de la création de la nature de prestation: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Mettre à jour une nature de prestation
     */
    public function updateNaturePrestation(Request $request, NaturePrestation $naturePrestation)
    {
        $request->validate([
            'nom' => 'required|max:100',
            'code' => 'required|max:20|unique:nature_prestations,code,' . $naturePrestation->id,
            'description' => 'nullable',
            'categorie_principale_id' => 'required|exists:categorie_principales,id',
            'est_actif' => 'boolean',
        ]);

        try {
            $naturePrestation->update($request->all());

            return redirect()->route('articles.index')
                ->with('success', 'Nature de prestation modifiée avec succès.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de la modification de la nature de prestation: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Supprimer une nature de prestation
     */
    public function destroyNaturePrestation(NaturePrestation $naturePrestation)
    {
        try {
            // Vérifier si la nature est utilisée
            if ($naturePrestation->categories()->count() > 0 || $naturePrestation->articles()->count() > 0) {
                return redirect()->back()
                    ->with('error', 'Impossible de supprimer cette nature de prestation car elle est utilisée.');
            }

            $naturePrestation->delete();

            return redirect()->route('articles.index')
                ->with('success', 'Nature de prestation supprimée avec succès.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Erreur lors de la suppression de la nature de prestation: ' . $e->getMessage());
        }
    }

    /**
     * Afficher un article (méthode optionnelle pour Resource Controller)
     */
    public function show(Article $article)
    {
        $article->load(['categorie', 'categoriePrincipale', 'naturePrestation', 'images']);

        return Inertia::render('Articles/Show', [
            'article' => $article,
        ]);
    }
    /**
 * Afficher un article
 */

    /**
     * Afficher le formulaire de création (méthode optionnelle)
     */
    public function create()
    {
        return Inertia::render('Articles/Create', [
            'categoriesPrincipales' => CategoriePrincipale::all(),
            'naturesPrestation' => NaturePrestation::all(),
            'categories' => Categorie::all(),
        ]);
    }

    /**
     * Afficher le formulaire d'édition (méthode optionnelle)
     */
    public function edit(Article $article)
    {
        $article->load(['categorie', 'categoriePrincipale', 'naturePrestation', 'images']);

        return Inertia::render('Articles/Edit', [
            'article' => $article,
            'categoriesPrincipales' => CategoriePrincipale::all(),
            'naturesPrestation' => NaturePrestation::all(),
            'categories' => Categorie::all(),
        ]);
    }
}