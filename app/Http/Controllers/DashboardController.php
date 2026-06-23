<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\BonCommande;
use App\Models\Demande;
use App\Models\FicheTechnique;
use App\Models\Fournisseur;
use App\Models\MouvementStock;
use App\Models\Reception;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $currentYear = now()->year;

        $ficheCollectivePerMonth = $this->monthlyCounts(
            FicheTechnique::collectivite()->whereYear('created_at', $currentYear),
            'created_at'
        );

        $marchesPerMonth = $this->monthlyCounts(
            BonCommande::query()->whereYear('created_at', $currentYear),
            'created_at'
        );

        $receptionsPerMonth = $this->monthlyCounts(
            Reception::query()->whereYear('created_at', $currentYear),
            'created_at'
        );

        $stockMovements = MouvementStock::query()
            ->select(
                DB::raw('MONTH(date_mouvement) as month'),
                DB::raw('SUM(COALESCE(quantite_entree, 0)) as entrees'),
                DB::raw('SUM(COALESCE(quantite_sortie, 0)) as sorties')
            )
            ->whereYear('date_mouvement', $currentYear)
            ->groupBy('month')
            ->get();

        $stockEntreesPerMonth = array_fill(1, 12, 0);
        $stockSortiesPerMonth = array_fill(1, 12, 0);
        foreach ($stockMovements as $row) {
            $stockEntreesPerMonth[(int) $row->month] = (float) $row->entrees;
            $stockSortiesPerMonth[(int) $row->month] = (float) $row->sorties;
        }

        $currentPrices = DB::table('bon_commande_articles')
            ->join('bon_commandes', 'bon_commandes.id', '=', 'bon_commande_articles.bon_commande_id')
            ->whereDate('bon_commandes.date_debut', '<=', today())
            ->whereDate('bon_commandes.date_fin', '>=', today())
            ->select(
                'bon_commande_articles.article_id',
                DB::raw('MAX(bon_commande_articles.prix_unitaire_ht) as prix_unitaire_ht')
            )
            ->groupBy('bon_commande_articles.article_id');

        $stockValue = Article::withNonExists()
            ->leftJoinSub($currentPrices, 'current_prices', function ($join) {
                $join->on('current_prices.article_id', '=', 'articles.id');
            })
            ->sum(DB::raw('articles.quantite_stock * COALESCE(current_prices.prix_unitaire_ht, 0)'));

        $bonCommandeStatus = BonCommande::query()
            ->selectRaw('statut, COUNT(*) as total')
            ->groupBy('statut')
            ->pluck('total', 'statut');

        $engagedAmount = (float) DB::table('bon_commande_articles')
            ->join('bon_commandes', 'bon_commandes.id', '=', 'bon_commande_articles.bon_commande_id')
            ->whereNotIn('bon_commandes.statut', [BonCommande::STATUT_CREE, BonCommande::STATUT_ANNULE])
            ->sum('bon_commande_articles.montant_ttc');

        $consumedAmount = (float) DB::table('receptions')
            ->join('bon_livraisons', 'bon_livraisons.id', '=', 'receptions.bon_livraison_id')
            ->join('bon_livraisons_items', 'bon_livraisons_items.bon_livraison_id', '=', 'bon_livraisons.id')
            ->sum(DB::raw('bon_livraisons_items.quantite * bon_livraisons_items.prix_unitaire * (1 + bon_livraisons_items.taux_tva / 100)'));

        $consumptionByMarket = $this->consumptionByMarket();
        $dashboardAlerts = $this->marketAlerts($consumptionByMarket);
        $categoryDistribution = BonCommande::query()
            ->join('categories', 'categories.id', '=', 'bon_commandes.categorie_id')
            ->select('categories.nom', DB::raw('COUNT(*) as total'))
            ->groupBy('categories.id', 'categories.nom')
            ->orderByDesc('total')
            ->take(8)
            ->get()
            ->map(fn ($row) => [
                'categorie' => $row->nom,
                'total' => (int) $row->total,
            ]);

        $topUsedArticles = MouvementStock::query()
            ->select('article_id', DB::raw('SUM(quantite_sortie) as total_sorties'))
            ->sorties()
            ->whereNotNull('article_id')
            ->groupBy('article_id')
            ->orderByDesc('total_sorties')
            ->with('article')
            ->take(10)
            ->get()
            ->filter(fn ($movement) => $movement->article)
            ->map(fn ($movement) => [
                'article_id' => $movement->article_id,
                'reference' => $movement->article->reference,
                'designation' => $movement->article->designation,
                'unite_mesure' => $movement->article->unite_mesure,
                'total_sorties' => (float) $movement->total_sorties,
            ])
            ->values();

        $recentDemandes = Demande::with('demandeur:id,name')
            ->latest()
            ->take(8)
            ->get(['id', 'numero', 'motif', 'statut', 'demandeur_id', 'created_at'])
            ->map(fn ($demande) => [
                'numero' => $demande->numero,
                'demandeur' => $demande->demandeur->name ?? 'N/A',
                'motif' => $demande->motif,
                'statut' => $demande->statut,
                'date' => $demande->created_at->format('Y-m-d'),
            ]);

        $recentSorties = MouvementStock::sorties()
            ->with('article:id,designation,unite_mesure')
            ->latest('date_mouvement')
            ->take(8)
            ->get()
            ->filter(fn ($movement) => $movement->article)
            ->map(fn ($movement) => [
                'date_sortie' => $movement->date_mouvement->format('Y-m-d'),
                'designation_article' => $movement->article->designation,
                'unite_mesure' => $movement->article->unite_mesure,
                'quantite_sortie' => (float) $movement->quantite_sortie,
            ])
            ->values();

        $recentEntrees = MouvementStock::entrees()
            ->with('article:id,designation,unite_mesure')
            ->latest('date_mouvement')
            ->take(8)
            ->get()
            ->filter(fn ($movement) => $movement->article)
            ->map(fn ($movement) => [
                'date_entree' => $movement->date_mouvement->format('Y-m-d'),
                'designation_article' => $movement->article->designation,
                'unite_mesure' => $movement->article->unite_mesure,
                'quantite_entree' => (float) $movement->quantite_entree,
            ])
            ->values();

        return Inertia::render('Dashboard', [
            'stats' => [
                'totalUsers' => User::count(),
                'activeFournisseurs' => Fournisseur::where('est_actif', true)->count(),
                'totalArticles' => Article::withNonExists()->count(),
                'activeArticles' => Article::withNonExists()->where('est_actif', true)->count(),
                'lowStockArticles' => Article::withNonExists()
                    ->where('quantite_stock', '>', 0)
                    ->whereColumn('quantite_stock', '<=', 'seuil_minimal')
                    ->count(),
                'ruptureArticles' => Article::withNonExists()->where('quantite_stock', '<=', 0)->count(),
                'totalBCs' => BonCommande::count(),
                'activeMarches' => BonCommande::current()
                    ->whereNotIn('statut', [BonCommande::STATUT_CREE, BonCommande::STATUT_ANNULE])
                    ->count(),
                'pendingMarches' => BonCommande::whereIn('statut', [
                    BonCommande::STATUT_CREE,
                    BonCommande::STATUT_ATTENTE_LIVRAISON,
                    BonCommande::STATUT_LIVRE_PARTIELLEMENT,
                ])->count(),
                'expiredMarches' => BonCommande::whereDate('date_fin', '<', today())
                    ->where('statut', '!=', BonCommande::STATUT_ANNULE)
                    ->count(),
                'expiringSoonMarches' => BonCommande::whereBetween('date_fin', [today(), today()->copy()->addDays(30)])
                    ->where('statut', '!=', BonCommande::STATUT_ANNULE)
                    ->count(),
                'engagedAmount' => round($engagedAmount, 2),
                'consumedAmount' => round($consumedAmount, 2),
                'remainingAmount' => round(max($engagedAmount - $consumedAmount, 0), 2),
                'pendingDemandes' => Demande::where('statut', 'en_attente_validation')->count(),
                'receptionsThisMonth' => Reception::whereBetween('created_at', [
                    now()->startOfMonth(),
                    now()->endOfMonth(),
                ])->count(),
                'stockValue' => round($stockValue, 2),
            ],
            'bonCommandeStatus' => $bonCommandeStatus,
            'topUsedArticles' => $topUsedArticles,
            'recentSorties' => $recentSorties,
            'recentEntrees' => $recentEntrees,
            'recentDemandes' => $recentDemandes,
            'ficheCollectivePerMonth' => array_values($ficheCollectivePerMonth),
            'marchesPerMonth' => array_values($marchesPerMonth),
            'receptionsPerMonth' => array_values($receptionsPerMonth),
            'stockEntreesPerMonth' => array_values($stockEntreesPerMonth),
            'stockSortiesPerMonth' => array_values($stockSortiesPerMonth),
            'consumptionByMarket' => $consumptionByMarket,
            'categoryDistribution' => $categoryDistribution,
            'dashboardAlerts' => $dashboardAlerts,
        ]);
    }

    private function consumptionByMarket()
    {
        $engagedByMarket = DB::table('bon_commande_articles')
            ->select('bon_commande_id', DB::raw('COALESCE(SUM(montant_ttc), 0) as engage'))
            ->groupBy('bon_commande_id');

        $consumedByMarket = DB::table('chef_commandes')
            ->join('bon_livraisons', 'bon_livraisons.chef_commande_id', '=', 'chef_commandes.id')
            ->join('receptions', 'receptions.bon_livraison_id', '=', 'bon_livraisons.id')
            ->join('bon_livraisons_items', 'bon_livraisons_items.bon_livraison_id', '=', 'bon_livraisons.id')
            ->whereNotNull('chef_commandes.bon_commande_id')
            ->select(
                'chef_commandes.bon_commande_id',
                DB::raw('COALESCE(SUM(bon_livraisons_items.quantite * bon_livraisons_items.prix_unitaire * (1 + bon_livraisons_items.taux_tva / 100)), 0) as consomme')
            )
            ->groupBy('chef_commandes.bon_commande_id');

        return DB::table('bon_commandes')
            ->leftJoinSub($engagedByMarket, 'engaged_by_market', function ($join) {
                $join->on('engaged_by_market.bon_commande_id', '=', 'bon_commandes.id');
            })
            ->leftJoinSub($consumedByMarket, 'consumed_by_market', function ($join) {
                $join->on('consumed_by_market.bon_commande_id', '=', 'bon_commandes.id');
            })
            ->select(
                'bon_commandes.id',
                'bon_commandes.reference',
                DB::raw('COALESCE(engaged_by_market.engage, 0) as engage'),
                DB::raw('COALESCE(consumed_by_market.consomme, 0) as consomme')
            )
            ->whereNotIn('bon_commandes.statut', [BonCommande::STATUT_CREE, BonCommande::STATUT_ANNULE])
            ->orderByDesc('consomme')
            ->take(10)
            ->get()
            ->map(function ($row) {
                $engage = (float) $row->engage;
                $consomme = (float) $row->consomme;

                return [
                    'id' => (int) $row->id,
                    'reference' => $row->reference,
                    'engage' => round($engage, 2),
                    'consomme' => round($consomme, 2),
                    'restant' => round(max($engage - $consomme, 0), 2),
                    'taux' => $engage > 0 ? round(($consomme / $engage) * 100, 1) : 0,
                ];
            })
            ->values();
    }

    private function marketAlerts($consumptionByMarket): array
    {
        $recentMarketIds = DB::table('receptions')
            ->join('bon_livraisons', 'bon_livraisons.id', '=', 'receptions.bon_livraison_id')
            ->join('chef_commandes', 'chef_commandes.id', '=', 'bon_livraisons.chef_commande_id')
            ->where('receptions.created_at', '>=', now()->subDays(30))
            ->whereNotNull('chef_commandes.bon_commande_id')
            ->distinct()
            ->pluck('chef_commandes.bon_commande_id');

        return [
            'expire_30_days' => BonCommande::whereBetween('date_fin', [today(), today()->copy()->addDays(30)])
                ->where('statut', '!=', BonCommande::STATUT_ANNULE)
                ->count(),
            'consumed_80' => $consumptionByMarket->where('taux', '>=', 80)->where('taux', '<', 90)->count(),
            'consumed_90' => $consumptionByMarket->where('taux', '>=', 90)->where('taux', '<', 100)->count(),
            'consumed_100' => $consumptionByMarket->where('taux', '>=', 100)->count(),
            'without_reception_30_days' => BonCommande::current()
                ->whereNotIn('statut', [BonCommande::STATUT_CREE, BonCommande::STATUT_ANNULE])
                ->whereNotIn('id', $recentMarketIds)
                ->count(),
        ];
    }

    private function monthlyCounts($query, string $dateColumn): array
    {
        $rows = $query
            ->select(DB::raw("MONTH($dateColumn) as month"), DB::raw('COUNT(*) as total'))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $values = array_fill(1, 12, 0);
        foreach ($rows as $row) {
            $values[(int) $row->month] = (int) $row->total;
        }

        return $values;
    }
}
