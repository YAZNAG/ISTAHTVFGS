<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Categorie;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Barryvdh\DomPDF\Facade\Pdf;

class ArticleStockController extends Controller implements HasMiddleware
{


    public static function middleware(): array
    {
        return [
            new Middleware('permission:articles_stocks', only: ['index', 'export']),
        ];
    }

    public function index(Request $request)
    {
        $query = Article::withNonExists()
            ->with(['categorie:id,nom'])
            ->select(['id', 'reference', 'designation', 'quantite_stock', 'seuil_minimal', 'unite_mesure', 'categorie_id']);

        $search = $request->search;
        if ($request->filled('search')) {

            $query->where(function ($query) use ($search) {
                $query->where('reference', 'like', '%' . $search . '%')
                    ->orWhere('designation', 'like', '%' . $search . '%');
            });

        }

        if ($request->filled('categorie')) {
            $query->where('categorie_id', $request->categorie);
        }

        $articles = $query->paginate(20)->withQueryString();

        $categories = Categorie::get(['id', 'nom']);

        return inertia('Stock/ArticlesStocks/Index', [
            'articles' => $articles,
            'categories' => $categories,
            'filters' => $request->only(['search', 'categorie']),
            'stats' => (function () {
                $row = Article::withNonExists()->selectRaw("
                    COUNT(*) as total,
                    COALESCE(SUM(quantite_stock), 0) as stockTotal,
                    SUM(CASE WHEN quantite_stock > 0 AND quantite_stock <= seuil_minimal * 0.8 THEN 1 ELSE 0 END) as lowStock,
                    SUM(CASE WHEN quantite_stock <= 0 THEN 1 ELSE 0 END) as rupture
                ")->first();
                return [
                    'total'      => (int) $row->total,
                    'stockTotal' => (float) $row->stockTotal,
                    'lowStock'   => (int) $row->lowStock,
                    'rupture'    => (int) $row->rupture,
                ];
            })(),
        ]);
    }

    public function export(Request $request)
    {
        $query = Article::withNonExists()
            ->with(['categorie:id,nom'])
            ->select(['id', 'reference', 'designation', 'quantite_stock', 'seuil_minimal', 'unite_mesure', 'categorie_id']);

        $now = now()->toDateTimeString();
        return Pdf::loadView('pdf.articles-stock', [
            'rows' => $query->get(),
            'now' => $now,
        ])->download("stock-articles-$now.pdf");
    }
}
