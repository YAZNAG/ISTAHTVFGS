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

        // -- Recent sorties
        $recentSorties = MouvementStock::sorties()->with([
            'article:id,designation,unite_mesure',
        ])->take(8)->get()->map(fn($ms) => [
            'date_sortie' => $ms->date_mouvement->format('Y-m-d'),
            'designation_article' => $ms->article->designation,
            'unite_mesure' => $ms->article->unite_mesure,
            'quantite_sortie' => $ms->quantite_sortie,
        ]);

        $recentEntrees = MouvementStock::entrees()->with([
            'article:id,designation,unite_mesure',
        ])->take(8)->get()->map(fn($ms) => [
            'date_sortie' => $ms->date_mouvement->format('Y-m-d'),
            'designation_article' => $ms->article->designation,
            'unite_mesure' => $ms->article->unite_mesure,
            'quantite_entree' => $ms->quantite_entree,
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
            'recentSorties' => $recentSorties,
            'recentEntrees' => $recentEntrees,
            'recentDemandes' => $recentDemandes,
        ]);
    }
}
