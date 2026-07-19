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
        $invenrtaires = Inventaire::with('categorie:id,nom')
            ->withCount([
                'lignes as articles_count',
                'lignes as filled_count' => fn ($q) => $q->whereNotNull('stock_reel'),
            ])
            ->when($request->semaine, function ($query) use ($request) {
                return $query->where('semaine', $request->semaine);
            })
            ->when($request->statut, function ($query) use ($request) {
                return $query->where('statut', $request->statut);
            })
            ->latest()
            ->paginate(10);

        return Inertia::render('Inventaire/Index', [
            'inventaires' => InventaireIndexResource::collection($invenrtaires),
            'filters' => $request->all('semaine', 'statut'),
            'categories' => \App\Models\Categorie::actives()->orderBy('nom')->get(['id', 'nom']),
        ]);
    }

    public function store(Request $request)
    {
        /* ---------- validation ----------
         * IMPORTANT : syntaxe TABLEAU obligatoire ici. Le regex contient des `|`
         * (0[1-9]|[1-4][0-9]|5[0-3]) ; en syntaxe pipe "a|regex:/.../|b" Laravel
         * decoupe la chaine sur ces `|` et casse le pattern -> preg_match(): No ending delimiter.
         */
        $request->validate([
            'semaine'      => ['required', 'regex:/^\d{4}-W(0[1-9]|[1-4][0-9]|5[0-3])$/'],
            'categorie_id' => ['nullable', 'exists:categories,id'],
        ], [
            'semaine.regex' => 'La semaine doit avoir le format AAAA-Wss (ex : 2026-W26).',
        ]);

        // Unicite par semaine + categorie (une categorie donnee OU "toutes categories" = null)
        $existe = Inventaire::where('semaine', $request->semaine)
            ->where('categorie_id', $request->categorie_id)   // null = toutes categories
            ->exists();

        if ($existe) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'semaine' => $request->categorie_id
                    ? 'Un inventaire existe déjà pour cette semaine et cette catégorie.'
                    : 'Un inventaire « toutes catégories » existe déjà pour cette semaine.',
            ]);
        }

        DB::transaction(function () use ($request) {
            [$isoYear, $isoWeek] = explode('-W', $request->semaine);

            $debut = Carbon::now()->setISODate((int) $isoYear, (int) $isoWeek)->startOfWeek();
            $fin = $debut->copy()->endOfWeek();

            /* ---------- header ---------- */
            $inventaire = Inventaire::create([
                'semaine'      => $request->semaine,
                'categorie_id' => $request->categorie_id,
                'mois'         => $debut->format('Y-m'),
                'statut'       => 'draft',
                'finalized_at' => null,
            ]);

            /* ---------- 3-query eager load (filtre categorie eventuel) ---------- */
            $articles = Article::when($request->categorie_id, fn ($q) => $q->where('categorie_id', $request->categorie_id))
                ->with([
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


            if ($rows->isNotEmpty()) {
                InventaireLigne::insert($rows->toArray());
            }
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
        if ($inventaire->statut === 'finalized') {
            return back()->with('error', 'Cet inventaire est déjà finalisé.');
        }

        DB::transaction(function () use ($inventaire) {
            // Les articles non saisis sont comptés a 0 (ecart = 0 - stock theorique)
            $inventaire->lignes()->whereNull('stock_reel')->update([
                'stock_reel' => 0,
                'ecart'      => DB::raw('-1 * stock_theorique'),
                'updated_at' => now(),
            ]);

            // Verrouillage : plus aucune modification possible apres finalisation
            $inventaire->update([
                'statut'       => 'finalized',
                'finalized_at' => now(),
            ]);
        });

        return redirect()
            ->route('inventaires.index')
            ->with('success', "Inventaire {$inventaire->semaine} finalisé avec succès (articles non saisis comptés à 0).");
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
        $inventaire->load(['categorie:id,nom', 'lignes' => fn ($q) => $q->orderBy('code_article')]);

        return Pdf::loadView('pdf.inventaire.inventaire', [
            'inventaire'   => $inventaire,
            'pdfHeaderSrc' => $this->pdfHeaderBase64(),
        ])
        ->setPaper('a4', 'landscape')
        ->download("inventaire-{$inventaire->semaine}.pdf");
    }
}
