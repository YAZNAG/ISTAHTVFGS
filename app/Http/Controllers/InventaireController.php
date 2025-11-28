<?php

namespace App\Http\Controllers;

use App\Http\Resources\InventaireIndexResource;
use App\Models\Article;
use App\Models\Inventaire;
use App\Models\InventaireLigne;
use App\Models\MouvementStock;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Spatie\LaravelPdf\Enums\Format;
use Spatie\LaravelPdf\Facades\Pdf;

use function Spatie\LaravelPdf\Support\pdf;

class InventaireController extends Controller implements HasMiddleware
{


    public static function middleware(): array
    {
        return [
            new Middleware('permission:list_inventaire', only: ['index']),
            new Middleware('permission:create_inventaire', only: ['store']),
            new Middleware('permission:fill_stock_reel', only: ['edit', 'updateLingne', 'finalize']),
            new Middleware('permission:unlock_inventaire', only: ['unlock']),
            new Middleware('permission:pdf_inventaire', only: ['generatePdf']),


        ];
    }

    public function index(Request $request)
    {
        $invenrtaires = Inventaire::with(['lignes'])
            ->when($request->mois, function ($query) use ($request) {
                return $query->where('mois', $request->mois);
            })
            ->when($request->statut, function ($query) use ($request) {
                return $query->where('statut', $request->statut);
            })
            ->paginate(10);

        return Inertia::render('Inventaire/Index', [
            'inventaires' => InventaireIndexResource::collection($invenrtaires),
            'filters' => $request->all('mois', 'statut'),
        ]);
    }

    public function store(Request $request)
    {
        /* ---------- validation ---------- */
        $request->validate([
            'mois' => 'required|date_format:Y-m|unique:inventaires,mois',
        ], [
            'mois.unique' => 'Un inventaire existe déjà pour ce mois.',
            'mois.date_format' => 'Le mois doit avoir le format YYYY-MM.',
        ]);

        DB::transaction(function () use ($request) {
            /* ---------- header ---------- */
            $inventaire = Inventaire::create([
                'mois'         => $request->mois,
                'statut'       => 'draft',
                'finalized_at' => null,
            ]);

            /* ---------- period ---------- */
            $debut = $request->mois . '-01';
            $fin   = Carbon::parse($debut)->endOfMonth();


            /* ---------- 3-query eager load ---------- */
            $articles = Article::with([
                /* opening stock : last movement BEFORE the month */
                'mouvementsStock' => fn($q) => $q->where('date_mouvement', '<', $debut)
                    ->latest('date_mouvement')
                    ->limit(1)
                    ->select('article_id', 'quantite_actuelle'),

                /* entries inside month */
                'mouvementsStockEntree' => fn($q) => $q->whereBetween('date_mouvement', [$debut, $fin])
                    ->selectRaw('article_id, SUM(quantite_entree) as total_entree')
                    ->groupBy('article_id'),

                /* exits inside month */
                'mouvementsStockSortie' => fn($q) => $q->whereBetween('date_mouvement', [$debut, $fin])
                    ->selectRaw('article_id, SUM(quantite_sortie) as total_sortie')
                    ->groupBy('article_id'),
            ])
                ->where('est_actif', true)
                ->get();

            /* ---------- build insert array ---------- */
            $rows = $articles->map(fn($art) => [
                'inventaire_id'    => $inventaire->id,
                'code_article'     => $art->reference,
                'designation'      => $art->designation,
                'unite_mesure'     => $art->unite_mesure,
                'qte_entree'       => (float) ($art->mouvementsStockEntree->first()->total_entree ?? 0),
                'qte_sortie'       => (float) ($art->mouvementsStockSortie->first()->total_sortie ?? 0),
                'stock_theorique'  => (float) (
                    ($art->mouvementsStock->first()->quantite_actuelle ?? 0)
                    + ($art->mouvementsStockEntree->first()->total_entree ?? 0)
                    - ($art->mouvementsStockSortie->first()->total_sortie ?? 0)
                ),
                'stock_reel'       => null,
                'ecart'            => 0,
                'observations'     => null,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);


            InventaireLigne::insert($rows->toArray());
        });


        /* ---------- redirect ---------- */
        return redirect()
            // ->route('inventaires.edit', $inventaire->id)
            ->back()
            ->with('success', 'Inventaire créé - veuillez renseigner les stocks réels.');
    }

    public function edit(Inventaire $inventaire)
    {

        // // optional: prevent viewing a soft-deleted or future month
        // abort_if($inventaire->statut === 'finalized' && ! request()->user()->can('inventaire.read-finalized'), 403);

        /* --- eager-load lines + article for quick access --- */
        $inventaire->load([
            'lignes' => fn($q) => $q->orderBy('code_article'),
        ]);

        return Inertia::render('Inventaire/Edit', [
            'inventaire' => $inventaire,   // header + lines collection
        ]);
    }

    public function updateLingne(Request $request, InventaireLigne $ligne)
    {
        // lock check
        abort_if($ligne->inventaire->statut === 'finalized', 403, 'Inventaire déjà finalisé.');

        // validate
        $validated = $request->validate([
            'stock_reel'   => 'required|numeric',
            'observations' => 'nullable|string|max:255',
        ]);

        // re-calculate écart server-side
        $validated['ecart'] = ($validated['stock_reel'] ?? 0) - $ligne->stock_theorique;

        // save
        $ligne->update($validated);

        // silent success
        return redirect()->back()->with('success', 'Ligne mise à jour.');
    }

    public function finalize(Inventaire $inventaire)
    {
        // 1. Vérifier que tous les stocks réels sont remplis
        if ($inventaire->lignes()->whereNull('stock_reel')->exists()) {
            return back()->with('error', 'Tous les articles doivent avoir un stock réel renseigné.');
        }

        // 2. Verrouiller l’inventaire
        $inventaire->update([
            'statut'       => 'finalized',
            'finalized_at' => now(),
        ]);

        // 4. Rediriger avec message de succès
        return redirect()
            ->route('inventaires.index')
            ->with('success', "Inventaire {$inventaire->mois} finalisé avec succès.");
    }

    public function unlock(Inventaire $inventaire)
    {

        $inventaire->update([
            'statut'       => 'draft',
            'finalized_at' => null,
        ]);

        // 4. Rediriger avec message de succès
        return redirect()
            ->back()
            ->with('success', "Inventaire {$inventaire->mois} déverrouillé.");
    }

    public function generatePdf(Inventaire $inventaire)
    {
        $inventaire->load('lignes');

        return Pdf::view('pdf.inventaire.inventaire', [
            'inventaire' => $inventaire
        ])->headerView('pdf.H')
            ->footerView('pdf.F')
            ->margins(45, 5, 40,5)
            ->format(Format::A4)->download("inventaire-{$inventaire->mois}.pdf");
    }
}
