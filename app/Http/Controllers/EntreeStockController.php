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
            'end_date' => 'nullable|date',
        ]);

        $startDate = Carbon::parse($request->start_date)->startOfDay();

        $query = MouvementStock::entrees()->with([
            'article',
            'referenceable',
        ]);

        $endDate = $request->end_date ? Carbon::parse($request->end_date)->endOfDay() : null;
        if ($request->end_date) {
            $data = $query->whereBetween('created_at', [$startDate, $endDate])->get();
        } else {
            // get data for entire month of start_date
            $data = $query->whereYear('created_at', $startDate->year)
                        ->whereMonth('created_at', $startDate->month)
                        ->get();
        }

        $articles = ExportEntreeStockRecource::collection($data)->toArray($request);

        return view('pdf.fiche-entree', 
                    compact('articles', 'startDate', 'endDate')
    );

        return Pdf::view('pdf.fiche-entree', 
                    compact('articles', 'startDate', 'endDate')
                )->download('fiche-entree.pdf');
    }
}