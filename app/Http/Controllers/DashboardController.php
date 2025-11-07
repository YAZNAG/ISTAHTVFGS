<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\BonCommande;
use App\Models\Demande;
use App\Models\Fournisseur;
use App\Models\MouvementStock;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Spatie\LaravelPdf\Facades\Pdf;

class DashboardController extends Controller
{
    public function index() {
        
        // ---- KPI Cards ----
        $totalUsers = User::count();
        $activeFournisseurs = Fournisseur::where('est_actif', true)->count();
        $totalArticles = Article::count();
        $totalBCs = BonCommande::count();
        $pendingDemandes = Demande::where('statut', 'en_attente_validation')->count();

        
        // Example stock value (sum of quantite_stock * prix_unitaire)
        // $totalStockValue = Article::sum(DB::raw('quantite_stock * prix_unitaire'));

        // ---- Bon Commande Status distribution ----
        $bonCommandeStatus = BonCommande::selectRaw('statut, COUNT(*) as total')
            ->groupBy('statut')
            ->pluck('total', 'statut');

        // ---- Top 5 Articles in Stock ----
        $topArticles = Article::orderByDesc('quantite_stock')
            ->take(5)
            ->get(['designation', 'quantite_stock']);
        

        // ---- Low Stock Articles ----
        $lowStockArticles = Article::whereColumn('quantite_stock', '<', 'seuil_minimal')
            ->get(['reference', 'designation', 'quantite_stock', 'seuil_minimal']);

        // ---- Max Stock Articles ----
        $overstockedArticles = Article::whereColumn('quantite_stock', '>', 'seuil_maximal')
            ->get(['reference', 'designation', 'quantite_stock', 'seuil_maximal']);

        $topUsedArticles = MouvementStock::select('article_id', DB::raw('SUM(quantite_sortie) as total_sorties'))
            ->sorties()
            ->groupBy('article_id')
            ->orderByDesc('total_sorties')
            ->with('article')
            ->take(10)
            ->get()
            ->map(function($ms) {
                return [
                    'article_id' => $ms->article_id,
                    'reference' => $ms->article->reference,
                    'designation' => $ms->article->designation,
                    'unite_mesure' => $ms->article->unite_mesure,
                    'total_sorties' => $ms->total_sorties,
                ];
            })
            ;

        // ---- Recent Demandes ----
        $recentDemandes = Demande::latest()
            ->take(8)
            ->get(['numero', 'motif', 'statut', 'created_at'])
            ->map(fn($d) => [
                'numero' => $d->numero,
                'demandeur' => $d->user->name ?? 'N/A',
                'motif' => $d->motif,
                'statut' => $d->statut,
                'date' => $d->created_at->format('Y-m-d')
            ]);



        return Inertia::render('Dashboard', [
            'stats' => [
                'totalUsers' => $totalUsers,
                'activeFournisseurs' => $activeFournisseurs,
                'totalArticles' => $totalArticles,
                'totalBCs' => $totalBCs,
                'pendingDemandes' => $pendingDemandes,
                // 'totalStockValue' => $totalStockValue,
            ],
            'bonCommandeStatus' => $bonCommandeStatus,
            'topUsedArticles' => $topUsedArticles,
            'lowStockArticles' => $lowStockArticles,
            'overstockedArticles' => $overstockedArticles,
            'recentDemandes' => $recentDemandes,
        ]);
    }
}
