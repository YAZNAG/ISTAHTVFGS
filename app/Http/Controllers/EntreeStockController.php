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
use Spatie\LaravelPdf\Enums\Format;
use Spatie\LaravelPdf\Facades\Pdf;

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
            'article',
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
            'filters' => $request->only(['start_date', 'end_date', 'search']),
        ]);
    }

    function createExport() 
    {
        
        return Inertia::modal('Stock/EntreeStocks/CreateExportModal')->baseRoute('entree-stocks.index');
    }

    public function export(Request $request) 
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);

        $startDate = Carbon::parse($request->start_date)->startOfDay();
        $endDate = Carbon::parse($request->end_date)->endOfDay();

        $data = MouvementStock::entrees()->with([
            'article',
            'referenceable',
        ])->whereBetween('created_at', [$startDate, $endDate])->get();

        $articles = ExportEntreeStockRecource::collection($data)->toArray($request);

        return Pdf::view('pdf.fiche-entree', 
                    compact('articles', 'startDate', 'endDate')
    )->headerView('pdf.H')
            ->footerView('pdf.F')
            ->margins(45, 5, 40,5)
            ->format(Format::A4)
            ->download('fiche-entree.pdf');
    }
}