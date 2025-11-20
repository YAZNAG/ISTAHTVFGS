<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Categorie;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ArticleStockController extends Controller implements HasMiddleware
{


    public static function middleware(): array
    {
        return [
            new Middleware('permission:articles_stocks', only: ['index']),
        ];
    }

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
