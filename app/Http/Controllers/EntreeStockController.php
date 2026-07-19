<?php
// app/Http/Controllers/EntreeStockController.php

namespace App\Http\Controllers;

use App\Exports\EntreeExport;
use App\Exports\FournisseursExport;
use App\Http\Resources\ExportEntreeStockRecource;
use App\Http\Resources\IndexEntreeStockRecource;
use App\Models\EntreeStock;
use App\Models\LigneEntreeStock;
use App\Models\Fournisseur;
use App\Models\Article;
use App\Models\BonReception;
use App\Models\MouvementStock;
use App\Models\Reception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class EntreeStockController extends Controller implements HasMiddleware
{


    public static function middleware(): array
    {
        return [
            new Middleware('permission:entree_stocks', only: ['index', 'export', 'createExport']),
        ];
    }
    
    /**
     * Display a listing of the resource.
     */
   // Dans EntreeStockController.php
    public function index(Request $request)
    {
        $query = MouvementStock::entrees()->with([
            'article:id,reference,designation,unite_mesure',
            'referenceable',
        ]);

        $search = $request->search;
        if ($request->filled('search')) {

            $query->whereHas('article', function ($q) use ($search) {
                $q->where('reference', 'like', '%' . $search . '%')
                    ->orWhere('designation', 'like', '%' . $search . '%');
            });

            $query->orWhereHasMorph('referenceable', Reception::class, function ($q) use ($search) {
                $q->where('numero', 'like', '%' . $search . '%');
            });

        }

        if ($request->filled('categorie_id')) {
            $query->whereHas('article', function ($q) use ($request) {
                $q->where('categorie_id', $request->categorie_id);
            });
        }

        // Filtrage par date
        if ($request->filled('start_date')) {
            $query->where('date_mouvement', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('date_mouvement', '<=', $request->end_date);
        }


        $entreeStocks = $query->paginate(20)->withQueryString();


        return inertia('Stock/EntreeStocks/Index', [
            'entrees' => IndexEntreeStockRecource::collection($entreeStocks),
            'filters' => $request->only(['start_date', 'end_date', 'search', 'categorie_id']),
            'categories' => \App\Models\Categorie::actives()->orderBy('nom')->get(['id', 'nom']),
        ]);
    }

    function createExport()
    {
        return Inertia::modal('Stock/EntreeStocks/CreateExportModal', [
            'categories' => \App\Models\Categorie::actives()->orderBy('nom')->get(['id', 'nom']),
        ])->baseRoute('entree-stocks.index');
    }

    public function export(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'categorie_id' => 'nullable|exists:categories,id',
        ]);

        $startDate = Carbon::parse($request->start_date)->startOfDay();
        $endDate = Carbon::parse($request->end_date)->endOfDay();

        $data = MouvementStock::entrees()->with([
            'article',
            'referenceable',
        ])
            ->when($request->filled('categorie_id'), function ($q) use ($request) {
                $q->whereHas('article', fn ($q2) => $q2->where('categorie_id', $request->categorie_id));
            })
            ->whereBetween('date_mouvement', [$startDate, $endDate])->get();

        $articles = ExportEntreeStockRecource::collection($data)->toArray($request);

        $categorie = $request->filled('categorie_id')
            ? \App\Models\Categorie::find($request->categorie_id)?->nom
            : null;

        return Pdf::loadView('pdf.fiche-entree', [
            'articles'     => $articles,
            'startDate'    => $startDate,
            'endDate'      => $endDate,
            'categorie'    => $categorie,
            'pdfHeaderSrc' => $this->pdfHeaderBase64(),
        ])
        ->setPaper('a4', 'landscape')
        ->download('fiche-entree.pdf');
    }
}