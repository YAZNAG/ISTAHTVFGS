<?php
// app/Http/Controllers/SortieStockController.php

namespace App\Http\Controllers;

use App\Enums\DemandeStatut;
use App\Http\Resources\ExportSortieStockRecource;
use App\Http\Resources\IndexSortieStockResource;
use App\Http\Resources\ShowSortieStockResource;
use App\Models\SortieStock;
use App\Models\LigneSortieStock;
use App\Models\Article;
use App\Models\Client;
use App\Models\Demande;
use App\Models\MouvementStock;
use App\Models\Reception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

class SortieStockController extends Controller implements HasMiddleware
{


    public static function middleware(): array
    {
        return [
            new Middleware('permission:sortie_stocks', only: ['index', 'export', 'createExport']),
        ];
    }
    
    /**
     * Display a listing of the resource.
     */
   // Dans EntreeStockController.php
    public function index(Request $request)
    {
        $query = MouvementStock::sorties()->with([
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


        $sortieStocks = $query->paginate(20)->withQueryString();


        return inertia('Stock/SortieStocks/Index', [
            'sorties' => IndexSortieStockResource::collection($sortieStocks),
            'filters' => $request->only(['start_date', 'end_date', 'search', 'categorie_id']),
            'categories' => \App\Models\Categorie::actives()->orderBy('nom')->get(['id', 'nom']),
        ]);
    }

    function createExport()
    {

        return Inertia::modal('Stock/SortieStocks/CreateExportModal')->baseRoute('sortie-stocks.index');
    }

    public function export(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);

        $startDate = Carbon::parse($request->start_date)->startOfDay();
        $endDate = Carbon::parse($request->end_date)->endOfDay();

        $data = MouvementStock::sorties()->with([
            'article',
            'referenceable',
        ])
            ->when($request->filled('categorie_id'), function ($q) use ($request) {
                $q->whereHas('article', fn ($q2) => $q2->where('categorie_id', $request->categorie_id));
            })
            ->whereBetween('date_mouvement', [$startDate, $endDate])->get();

        $articles = ExportSortieStockRecource::collection($data)->toArray($request);

        return Pdf::loadView('pdf.fiche-sortie', 
                        compact('articles', 'startDate', 'endDate')
            )
            ->download('fiche-sortie.pdf');
    }
}