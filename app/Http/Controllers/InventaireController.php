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
use Barryvdh\DomPDF\Facade\Pdf;


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
            ->when($request->semaine, function ($query) use ($request) {
                return $query->where('semaine', $request->semaine);
            })
            ->when($request->statut, function ($query) use ($request) {
                return $query->where('statut', $request->statut);
            })
            ->paginate(10);

        return Inertia::render('Inventaire/Index', [
            'inventaires' => InventaireIndexResource::collection($invenrtaires),
            'filters' => $request->all('semaine', 'statut'),
        ]);
    }

    public function store(Request $request)
    {
        /* ---------- validation ---------- */
        $request->validate([
            'semaine' => 'required|regex:/^\d{4}-W(0[1-9]|[1-4][0-9]|5[0-3])$/|unique:inventaires,semaine',
        ], [
            'semaine.unique' => 'Un inventaire existe déjà pour cette semaine.',
            'semaine.regex' => 'La semaine doit avoir le format AAAA-Wss (ex: 2026-W26).',
        ]);

        DB::transaction(function () use ($request) {
            [$isoYear, $isoWeek] = explode('-W', $request->semaine);

            $debut = Carbon::now()->setISODate((int) $isoYear, (int) $isoWeek)->startOfWeek();
            $fin = $debut->copy()->endOfWeek();

            /* ---------- header ---------- */
            $inventaire = Inventaire::create([
                'semaine'      => $request->semaine,
                'mois'         => $debut->format('Y-m'),
                'statut'       => 'draft',
                'finalized_at' => null,
            ]);

            /* ---------- 3-query eager load ---------- */
            $articles = Article::with([
                /* opening stock : last movement BEFORE the week */
                'mouvementsStock' => fn($q) => $q->where('date_mouvement', '<', $debut)
                    ->latest('date_mouvement')
                    ->limit(1)
                    ->select('article_id', 'quantite_actuelle'),

                /* entries inside the week */
                'mouvementsStockEntree' => fn($q) => $q->whereBetween('date_mouvement', [$debut, $fin])
                    ->selectRaw('article_id, SUM(quantite_entree) as total_entree')
                    ->groupBy('article_id'),

                /* exits inside the week */
                'mouvementsStockSortie' => fn($q) => $q->whereBetween('date_mouvement', [$debut, $fin])
                    ->selectRaw('article_id, SUM(quantite_sortie) as total_sortie')
                    ->groupBy('article_id'),
            ])
                ->where('est_actif', true)
                ->get();

            /* ---------- build insert array (uniquement stock theorique > 0) ---------- */
            $rows = $articles
                ->map(function ($art) use ($inventaire) {
                    $stockTheorique = (float) (
                        ($art->mouvementsStock->first()->quantite_actuelle ?? 0)
                        + ($art->mouvementsStockEntree->first()->total_entree ?? 0)
                        - ($art->mouvementsStockSortie->first()->total_sortie ?? 0)
                    );

                    return [
                        'inventaire_id'    => $inventaire->id,
                        'code_article'     => $art->reference,
                        'designation'      => $art->designation,
                        'unite_mesure'     => $art->unite_mesure,
                        'qte_entree'       => (float) ($art->mouvementsStockEntree->first()->total_entree ?? 0),
                        'qte_sortie'       => (float) ($art->mouvementsStockSortie->first()->total_sortie ?? 0),
                        'stock_theorique'  => $stockTheorique,
                        'stock_reel'       => null,
                        'ecart'            => 0,
                        'observations'     => null,
                        'created_at'       => now(),
                        'updated_at'       => now(),
                    ];
                })
                ->filter(fn ($row) => $row['stock_theorique'] > 0)
                ->values();


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
            ->with('success', "Inventaire {$inventaire->semaine} finalisé avec succès.");
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
            ->with('success', "Inventaire {$inventaire->semaine} déverrouillé.");
    }

    public function generatePdf(Inventaire $inventaire)
    {
        $inventaire->load('lignes');

        return Pdf::loadView('pdf.inventaire.inventaire', [
            'inventaire' => $inventaire
        ])->download("inventaire-{$inventaire->semaine}.pdf");
    }
}
