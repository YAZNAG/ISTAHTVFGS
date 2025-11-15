<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Categorie;
use Illuminate\Http\Request;

class ArticleStockController extends Controller
{
    public function index(Request $request)
    {
        $query = Article::withNonExists()->with(['categorie:id,nom'])->select(['id', 'reference','designation', 'quantite_stock', 'unite_mesure', 'categorie_id']);

        $search = $request->search;
        if ($request->filled('search')) {

            $query->where('reference', 'like', '%' . $search . '%')
                ->orWhere('designation', 'like', '%' . $search . '%');

        }

        if ($request->filled('categorie')) {
            $query->where('categorie_id', $request->categorie);
        }

        $articles = $query->paginate(20)->withQueryString();

        $categories = Categorie::get(['id', 'nom']);

        return inertia('Stock/ArticlesStocks/Index', [
            'articles' => $articles,
            'categories' => $categories,
            'filters' => $request->only(['search']),
        ]);
    }
}
