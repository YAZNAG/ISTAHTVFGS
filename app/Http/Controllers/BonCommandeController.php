<?php

namespace App\Http\Controllers;

use App\Exports\MarchesExport;
use App\Http\Resources\ListBonCommandesExport;
use App\Http\Resources\MarcheValidateResource;
use App\Models\Article;
use App\Models\BonCommande;
use App\Models\BonCommandeArticle;
use App\Models\BonLivraison;
use App\Models\Categorie;
use App\Models\ChefCommande;
use App\Models\Decompte;
use App\Models\Fournisseur;
use App\Models\HistoriqueStatutBc;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class BonCommandeController extends Controller implements HasMiddleware
{
    private array $tauxTVA = [0, 5, 7, 10, 14, 20];

    public static function middleware(): array
    {
        return [
            new Middleware('permission:list_marches', only: ['index']),
            new Middleware('permission:show_marches', only: ['show']),
            new Middleware('permission:create_marches', only: ['create', 'store']),
            new Middleware('permission:validate_marches', only: ['edit', 'updateStatut', 'annuler']),
            new Middleware('permission:edit_marches', only: ['modify', 'update', 'updateModify', 'destroy']),
            new Middleware('permission:pdf_marches', only: ['generatePdf']),
            new Middleware('permission:export_marches', only: ['export', 'exportPdf', 'exportExcel']),
        ];
    }

    public function index(Request $request)
    {
        $marches = $this->filteredMarches($request)
            ->with($this->indexRelations())
            ->latest()
            ->paginate(9)
            ->withQueryString()
            ->through(fn (BonCommande $marche) => $this->marketIndexPayload($marche));

        return Inertia::render('Achats/BonCommandes/Index', [
            'marches' => $marches,
            'categories' => $this->categoryOptions(),
            'fournisseurs' => Fournisseur::query()
                ->orderBy('raison_sociale')
                ->orderBy('nom')
                ->get(['id', 'nom', 'raison_sociale']),
            'filters' => $request->only(['search', 'reference', 'objet', 'statut', 'categorie_id', 'fournisseur_id', 'date']),
            'stats' => $this->getStats(),
        ]);
    }

    public function create()
    {
        return Inertia::modal('Achats/BonCommandes/CreateModal', [
            'tauxTVA' => $this->tauxTVA,
            'articles' => $this->activeArticleOptions(),
            'categories' => $this->categoryOptions(),
        ])->baseRoute('bon-commandes.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'reference' => 'required|unique:bon_commandes',
            'objet' => 'required|string|max:255',
            'description' => 'nullable|string',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after_or_equal:date_debut',
            'categorie_id' => 'required|exists:categories,id',
            'date_mise_ligne' => 'required|date',
            'date_limite_reception' => 'required|date|after_or_equal:date_mise_ligne',
            'notes' => 'nullable|string',
            'pieces_jointes.*' => 'nullable|file|max:10240',
            'articles' => 'required|array|min:1',
            'articles.*.article_id' => 'required|exists:articles,id',
            'articles.*.quantite_minimale' => 'nullable|numeric|min:0',
            'articles.*.quantite_maximale' => 'nullable|numeric|min:0.01',
            'articles.*.quantite_commandee' => 'nullable|numeric|min:0.01',
            'articles.*.taux_tva' => 'required|numeric|min:0',
        ]);

        $this->validateArticlesBelongToCategory((int) $request->categorie_id, $request->articles);

        DB::transaction(function () use ($request) {
            $categorie = Categorie::findOrFail($request->categorie_id);
            $piecesJointes = [];

            if ($request->hasFile('pieces_jointes')) {
                foreach ($request->file('pieces_jointes') as $file) {
                    $piecesJointes[] = $file->store('pieces_jointes/bon_commandes', 'public');
                }
            }

            $bonCommande = BonCommande::create($this->bonCommandeFieldsForCategory([
                'reference' => trim($request->reference),
                'objet' => trim($request->objet),
                'description' => $request->description,
                'date_debut' => $request->date_debut,
                'date_fin' => $request->date_fin,
                'date_mise_ligne' => $request->date_mise_ligne,
                'date_limite_reception' => $request->date_limite_reception,
                'pieces_jointes' => $piecesJointes,
                'notes' => $request->notes,
                'created_by' => auth()->id(),
                'statut' => BonCommande::STATUT_CREE,
            ], $categorie));

            foreach ($request->articles as $article) {
                $bonCommande->articles()->create($this->marketArticlePayload($article));
            }

            $this->recordStatusHistory($bonCommande, 'nouveau', BonCommande::STATUT_CREE, 'Creation du marche');
        });

        return redirect()->route('bon-commandes.index')
            ->with('success', 'Le marche a ete cree avec succes.');
    }

    public function modify(BonCommande $bonCommande)
    {
        if ($bonCommande->statut !== BonCommande::STATUT_CREE) {
            return redirect()
                ->route('bon-commandes.index')
                ->with('warning', 'Seuls les marches au statut cree peuvent etre modifies.');
        }

        $bonCommande->loadMissing('articles.article');

        return Inertia::modal('Achats/BonCommandes/EditModal', [
            'tauxTVA' => $this->tauxTVA,
            'articles' => $this->activeArticleOptions(),
            'categories' => $this->categoryOptions(),
            'marche' => [
                'id' => $bonCommande->id,
                'reference' => $bonCommande->reference,
                'objet' => $bonCommande->objet,
                'description' => $bonCommande->description,
                'date_debut' => optional($bonCommande->date_debut)->toDateString(),
                'date_fin' => optional($bonCommande->date_fin)->toDateString(),
                'date_mise_ligne' => optional($bonCommande->date_mise_ligne)->toDateString(),
                'date_limite_reception' => optional($bonCommande->date_limite_reception)->toDateString(),
                'categorie_id' => $bonCommande->categorie_id,
                'articles' => $bonCommande->articles->map(fn (BonCommandeArticle $articleLine) => [
                    'id' => $articleLine->article_id,
                    'designation' => $articleLine->article?->designation,
                    'unite_mesure' => $articleLine->article?->unite_mesure,
                    'quantite_minimale' => (float) ($articleLine->quantite_minimale ?? 0),
                    'quantite_maximale' => (float) ($articleLine->quantite_maximale ?? $articleLine->quantite_commandee),
                    'quantite_commandee' => (float) $articleLine->quantite_commandee,
                    'taux_tva' => (float) $articleLine->taux_tva,
                ]),
            ],
        ])->baseRoute('bon-commandes.index');
    }

    public function updateModify(Request $request, BonCommande $bonCommande)
    {
        if ($bonCommande->statut !== BonCommande::STATUT_CREE) {
            return back()->withErrors([
                'statut' => 'Impossible de modifier un marche qui n est plus au statut cree.',
            ]);
        }

        $request->validate([
            'reference' => 'required|unique:bon_commandes,reference,'.$bonCommande->id,
            'objet' => 'required|string|max:255',
            'description' => 'nullable|string',
            'date_debut' => 'required|date',
            'date_fin' => 'required|date|after_or_equal:date_debut',
            'categorie_id' => 'required|exists:categories,id',
            'date_mise_ligne' => 'required|date',
            'date_limite_reception' => 'required|date|after_or_equal:date_mise_ligne',
            'articles' => 'required|array|min:1',
            'articles.*.article_id' => 'required|exists:articles,id',
            'articles.*.quantite_minimale' => 'nullable|numeric|min:0',
            'articles.*.quantite_maximale' => 'nullable|numeric|min:0.01',
            'articles.*.quantite_commandee' => 'nullable|numeric|min:0.01',
            'articles.*.taux_tva' => 'required|numeric|min:0',
        ]);

        $this->validateArticlesBelongToCategory((int) $request->categorie_id, $request->articles);

        DB::transaction(function () use ($request, $bonCommande) {
            $ancienStatut = $bonCommande->statut;
            $categorie = Categorie::findOrFail($request->categorie_id);

            $bonCommande->update($this->bonCommandeFieldsForCategory([
                'reference' => trim($request->reference),
                'objet' => trim($request->objet),
                'description' => $request->description,
                'date_debut' => $request->date_debut,
                'date_fin' => $request->date_fin,
                'date_mise_ligne' => $request->date_mise_ligne,
                'date_limite_reception' => $request->date_limite_reception,
            ], $categorie));

            $bonCommande->articles()->delete();

            foreach ($request->articles as $article) {
                $bonCommande->articles()->create($this->marketArticlePayload($article));
            }

            $this->recordStatusHistory($bonCommande, $ancienStatut, $bonCommande->statut, 'Modification du marche');
        });

        return redirect()->route('bon-commandes.index')
            ->with('success', 'Le marche a ete modifie avec succes.');
    }

    public function update(Request $request, BonCommande $bonCommande)
    {
        return $this->updateModify($request, $bonCommande);
    }

    public function edit(BonCommande $bonCommande)
    {
        $bonCommande->loadMissing(['categorie', 'articles.article']);

        return Inertia::modal('Achats/BonCommandes/ValidateMarcheModal', [
            'marche' => MarcheValidateResource::make($bonCommande),
            'fournisseurs' => Fournisseur::where('est_actif', true)->orderBy('raison_sociale')->orderBy('nom')->get(),
        ])->baseRoute('bon-commandes.index');
    }

    public function updateStatut(Request $request, BonCommande $bonCommande)
    {
        $bonCommande->load('articles');

        if ($request->statut === BonCommande::STATUT_ANNULE) {
            $request->validate([
                'statut' => 'required|in:annule',
                'raison' => 'required|string|min:20',
            ]);

            if (! in_array($bonCommande->statut, [BonCommande::STATUT_CREE, BonCommande::STATUT_ATTENTE_LIVRAISON], true)) {
                return back()->withErrors([
                    'statut' => 'Impossible d annuler un marche deja livre.',
                ]);
            }
        } else {
            $request->validate([
                'statut' => 'required|in:attente_livraison,livre_completement,livre_partiellement',
                'fournisseur_id' => 'required|exists:fournisseurs,id',
                'articles' => 'required|array|min:1',
                'articles.*.id' => 'required|exists:bon_commande_articles,id',
                'articles.*.prix_unitaire_ht' => 'required|numeric|min:0',
            ]);
        }

        try {
            DB::beginTransaction();

            $ancienStatut = $bonCommande->statut;

            if ($request->statut === BonCommande::STATUT_ANNULE) {
                $bonCommande->update([
                    'statut' => BonCommande::STATUT_ANNULE,
                    'annule_at' => now(),
                    'reason_annulation' => $request->raison,
                ]);
            } else {
                $this->updateArticlesPrix($bonCommande, $request);

                $bonCommande->update([
                    'statut' => BonCommande::STATUT_ATTENTE_LIVRAISON,
                    'fournisseur_id' => $request->fournisseur_id,
                ]);
            }

            $this->recordStatusHistory(
                $bonCommande,
                $ancienStatut,
                $bonCommande->statut,
                $request->raison ?? 'Attribution fournisseur et saisie des prix'
            );

            DB::commit();

            return back()->with('success', 'Le statut du marche a ete mis a jour.');
        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error('Erreur mise a jour marche', [
                'bon_commande_id' => $bonCommande->id,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors([
                'error' => 'Une erreur est survenue lors de la mise a jour: '.$e->getMessage(),
            ]);
        }
    }

    public function show(BonCommande $bonCommande)
    {
        $bonCommande->load([
            'categorie',
            'fournisseur',
            'articles.article',
            'bonReceptions.lignesReception.article',
            'decomptes.items.article',
            'chefCommandes.items.article',
            'chefCommandes.user',
            'chefCommandes.livraisons.items',
            'chefCommandes.livraisons.reception' => fn ($query) => $query
                ->without(['media', 'bonLivraison'])
                ->select(['id', 'bon_livraison_id', 'numero', 'created_at']),
        ]);

        $bonLivraisons = $this->relatedBonLivraisons($bonCommande)
            ->sortByDesc(fn (BonLivraison $livraison) => $livraison->date_livraison ?: $livraison->created_at)
            ->values();

        return Inertia::render('Achats/BonCommandes/Show', [
            'marche' => $this->marketDetailPayload($bonCommande, $bonLivraisons),
            'decomptes' => $this->decomptePayload($bonCommande),
        ]);
    }

    public function destroy(BonCommande $bonCommande)
    {
        $bonCommande->loadMissing(['bonReceptions', 'decomptes', 'chefCommandes']);
        $hasModernDeliveries = $this->relatedBonLivraisons($bonCommande)->isNotEmpty();
        $hasOperationalData = $bonCommande->fournisseur_id
            || $bonCommande->bonReceptions->isNotEmpty()
            || $bonCommande->decomptes->isNotEmpty()
            || $bonCommande->chefCommandes->isNotEmpty()
            || $hasModernDeliveries;

        if ($hasOperationalData) {
            $oldStatus = $bonCommande->statut;

            $bonCommande->update([
                'statut' => BonCommande::STATUT_ANNULE,
                'annule_at' => now(),
                'reason_annulation' => 'Desactivation administrative: marche avec donnees operationnelles.',
            ]);

            $this->recordStatusHistory($bonCommande, $oldStatus, BonCommande::STATUT_ANNULE, 'Desactivation administrative');

            return redirect()
                ->back()
                ->with('warning', 'Ce marche contient des donnees operationnelles. Il a ete desactive au lieu d etre supprime.');
        }

        $reference = $bonCommande->reference;
        $bonCommande->articles()->delete();
        $bonCommande->delete();

        return redirect()
            ->route('bon-commandes.index')
            ->with('success', "Le marche {$reference} a ete supprime.");
    }

    public function generatePdf(BonCommande $bonCommande)
    {
        ini_set('memory_limit', '256M');

        if (in_array($bonCommande->statut, [BonCommande::STATUT_CREE, BonCommande::STATUT_ANNULE], true)) {
            abort(403, 'PDF non disponible pour ce statut');
        }

        $bonCommande->load([
            'articles.article.categorie',
            'categorie',
            'fournisseur',
        ]);

        $cleanReference = preg_replace('/[\/\\\\]/', '-', $bonCommande->reference);

        return Pdf::loadView('pdf.bon-commande.bon-commande', [
            'bonCommande' => $bonCommande,
            'articles' => $bonCommande->articles,
            'fournisseur' => $bonCommande->fournisseur,
        ])
            ->setPaper('a4', 'portrait')
            ->download("appel-offre-{$cleanReference}.pdf");
    }

    public function export(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'nullable|date',
        ]);

        $startDate = Carbon::parse($request->start_date)->startOfDay();
        $endDate = $request->end_date ? Carbon::parse($request->end_date)->endOfDay() : null;

        $query = BonCommande::with(['categorie', 'fournisseur'])->orderBy('created_at', 'desc');

        $data = $endDate
            ? $query->whereBetween('created_at', [$startDate, $endDate])->get()
            : $query->whereDate('created_at', '>=', $startDate)->get();

        return Pdf::loadView('pdf.list-bon-commandes', [
            'bonCommandes' => ListBonCommandesExport::collection($data)->toArray($request),
            'startDate' => $startDate,
            'endDate' => $endDate,
        ])
            ->download('list-bon-commandes.pdf');
    }

    public function exportExcel(Request $request)
    {
        return Excel::download(new MarchesExport($request->only([
            'search',
            'reference',
            'objet',
            'statut',
            'categorie_id',
            'fournisseur_id',
            'date',
        ])), 'marches.xlsx');
    }

    public function exportPdf(Request $request)
    {
        ini_set('memory_limit', '256M');

        $marches = $this->filteredMarches($request)
            ->with($this->indexRelations())
            ->latest()
            ->get()
            ->map(fn (BonCommande $marche) => $this->marketIndexPayload($marche));

        return Pdf::loadView('pdf.marches', [
            'marches' => $marches,
            'generatedAt' => now(),
        ])
            ->setPaper('a4', 'landscape')
            ->download('marches.pdf');
    }

    public function annuler(Request $request, BonCommande $bonCommande)
    {
        $request->validate([
            'raison' => 'required|string|min:20',
        ]);

        if (! in_array($bonCommande->statut, [BonCommande::STATUT_CREE, BonCommande::STATUT_ATTENTE_LIVRAISON], true)) {
            return back()->withErrors([
                'statut' => 'Impossible d annuler un marche deja livre.',
            ]);
        }

        // Note : l'annulation (changement de statut) reste autorisee meme si une reception existe.
        // C'est uniquement la suppression physique (destroy()) qui est bloquee dans ce cas (cf. hasOperationalData).
        DB::transaction(function () use ($request, $bonCommande) {
            $ancienStatut = $bonCommande->statut;

            $bonCommande->update([
                'statut' => BonCommande::STATUT_ANNULE,
                'annule_at' => now(),
                'reason_annulation' => $request->raison,
            ]);

            $this->recordStatusHistory($bonCommande, $ancienStatut, BonCommande::STATUT_ANNULE, $request->raison);
        });

        return redirect()->route('bon-commandes.index')
            ->with('success', 'Le marche a ete annule.');
    }

    private function filteredMarches(Request $request): Builder
    {
        $search = $request->input('search') ?: $request->input('reference') ?: $request->input('objet');

        return BonCommande::query()
            ->when($search, function (Builder $query) use ($search) {
                $query->where(function (Builder $query) use ($search) {
                    $query->where('reference', 'like', "%{$search}%")
                        ->orWhere('objet', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('statut'), fn (Builder $query) => $query->where('statut', $request->statut))
            ->when($request->filled('categorie_id'), fn (Builder $query) => $query->where('categorie_id', $request->categorie_id))
            ->when($request->filled('fournisseur_id'), fn (Builder $query) => $query->where('fournisseur_id', $request->fournisseur_id))
            ->when($request->filled('date'), function (Builder $query) use ($request) {
                $date = Carbon::parse($request->date)->toDateString();

                $query->whereDate('date_debut', '<=', $date)
                    ->whereDate('date_fin', '>=', $date);
            })
            ->when($request->filled('date_limite'), fn (Builder $query) => $query->whereDate('date_limite_reception', '>=', $request->date_limite));
    }

    private function indexRelations(): array
    {
        return [
            'categorie:id,nom,code,couleur',
            'fournisseur:id,nom,raison_sociale,contact,ville',
            'articles:id,bon_commande_id,article_id,quantite_minimale,quantite_maximale,quantite_commandee,taux_tva,prix_unitaire_ht,montant_ht,montant_tva,montant_ttc',
            'articles.article:id,reference,designation,unite_mesure',
            'bonReceptions:id,bon_commande_id,date_reception,fichier_bonlivraison',
            'bonReceptions.lignesReception:id,bon_reception_id,article_id,quantite_receptionnee,prix_total,montant_tva',
            'decomptes:id,marche_id,date,final',
            'decomptes.items:id,decompte_id,article_id,montant_ttc',
            'chefCommandes:id,bon_commande_id,user_id,numero,statut,created_at',
            'chefCommandes.items:id,chef_commande_id,article_id',
            'chefCommandes.user:id,name',
            'chefCommandes.livraisons:id,chef_commande_id,numero,statut,date_livraison,created_at',
            'chefCommandes.livraisons.items:id,bon_livraison_id,article_id,quantite,prix_unitaire,taux_tva',
            'chefCommandes.livraisons.reception' => fn ($query) => $query
                ->without(['media', 'bonLivraison'])
                ->select(['id', 'bon_livraison_id', 'numero', 'created_at']),
        ];
    }

    private function getStats(): array
    {
        $today = today();

        $marketIds = BonCommande::query()->pluck('id');
        $decompteAmounts = Decompte::query()
            ->join('decompte_items', 'decompte_items.decompte_id', '=', 'decomptes.id')
            ->select('decomptes.marche_id', DB::raw('COALESCE(SUM(decompte_items.montant_ttc), 0) as total_ttc'))
            ->groupBy('decomptes.marche_id')
            ->pluck('total_ttc', 'marche_id');

        $legacyReceptionAmounts = DB::table('bon_receptions')
            ->join('ligne_receptions', 'ligne_receptions.bon_reception_id', '=', 'bon_receptions.id')
            ->whereNull('bon_receptions.deleted_at')
            ->select('bon_receptions.bon_commande_id', DB::raw('COALESCE(SUM(COALESCE(ligne_receptions.prix_total, 0) + COALESCE(ligne_receptions.montant_tva, 0)), 0) as total_ttc'))
            ->groupBy('bon_receptions.bon_commande_id')
            ->pluck('total_ttc', 'bon_commande_id');

        $bonLivraisonAmounts = ChefCommande::query()
            ->join('bon_livraisons', 'bon_livraisons.chef_commande_id', '=', 'chef_commandes.id')
            ->join('bon_livraisons_items', 'bon_livraisons_items.bon_livraison_id', '=', 'bon_livraisons.id')
            ->select('chef_commandes.bon_commande_id', DB::raw('COALESCE(SUM(bon_livraisons_items.quantite * bon_livraisons_items.prix_unitaire * (1 + bon_livraisons_items.taux_tva / 100)), 0) as total_ttc'))
            ->groupBy('chef_commandes.bon_commande_id')
            ->pluck('total_ttc', 'bon_commande_id');

        $totalConsomme = $marketIds->sum(function ($marketId) use ($decompteAmounts, $legacyReceptionAmounts, $bonLivraisonAmounts) {
            $decompteAmount = (float) ($decompteAmounts[$marketId] ?? 0);
            $legacyReceptionAmount = (float) ($legacyReceptionAmounts[$marketId] ?? 0);
            $bonLivraisonAmount = (float) ($bonLivraisonAmounts[$marketId] ?? 0);

            return $decompteAmount > 0
                ? $decompteAmount
                : max($legacyReceptionAmount, $bonLivraisonAmount);
        });

        $totalEngage = (float) BonCommandeArticle::query()->sum('montant_ttc');

        return [
            'total' => $marketIds->count(),
            'actifs' => BonCommande::query()
                ->whereNotIn('statut', [BonCommande::STATUT_ANNULE, BonCommande::STATUT_LIVRE_COMPLETEMENT])
                ->where(fn (Builder $query) => $query->whereNull('date_fin')->orWhereDate('date_fin', '>=', today()))
                ->count(),
            'attente_livraison' => BonCommande::where('statut', BonCommande::STATUT_ATTENTE_LIVRAISON)->count(),
            'expires' => BonCommande::whereDate('date_fin', '<', $today)->count(),
            'clotures' => BonCommande::where('statut', BonCommande::STATUT_LIVRE_COMPLETEMENT)->count(),
            'montant_engage' => round($totalEngage, 2),
            'montant_consomme' => round($totalConsomme, 2),
            'montant_restant' => round(max($totalEngage - $totalConsomme, 0), 2),
        ];
    }

    private function categoryOptions()
    {
        return Categorie::query()
            ->where('est_actif', true)
            ->orderBy('nom')
            ->get(['id', 'nom', 'code', 'couleur']);
    }

    private function activeArticleOptions()
    {
        return Article::withNonExists()
            ->where('est_actif', true)
            ->orderBy('designation')
            ->get(['id', 'reference', 'designation', 'categorie_id', 'unite_mesure']);
    }

    private function bonCommandeFieldsForCategory(array $payload, Categorie $categorie): array
    {
        $payload['categorie_id'] = $categorie->id;

        return $payload;
    }

    private function marketArticlePayload(array $article): array
    {
        $quantiteMinimale = (float) ($article['quantite_minimale'] ?? 0);
        $quantiteMaximale = (float) ($article['quantite_maximale'] ?? $article['quantite_commandee'] ?? 0);

        if ($quantiteMaximale <= 0 || $quantiteMinimale > $quantiteMaximale) {
            throw ValidationException::withMessages([
                'articles' => 'Chaque article doit avoir une quantite maximale positive et superieure ou egale a la quantite minimale.',
            ]);
        }

        return [
            'article_id' => $article['article_id'],
            'quantite_commandee' => $quantiteMaximale,
            'quantite_minimale' => $quantiteMinimale,
            'quantite_maximale' => $quantiteMaximale,
            'taux_tva' => $article['taux_tva'],
            'prix_unitaire_ht' => null,
            'montant_ht' => null,
            'montant_tva' => null,
            'montant_ttc' => null,
        ];
    }

    private function validateArticlesBelongToCategory(int $categorieId, array $articles): void
    {
        $articleIds = collect($articles)->pluck('article_id')->filter()->unique()->values();

        $invalidArticles = Article::query()
            ->whereIn('id', $articleIds)
            ->where('categorie_id', '!=', $categorieId)
            ->pluck('designation');

        if ($invalidArticles->isNotEmpty()) {
            throw ValidationException::withMessages([
                'articles' => 'Articles hors categorie du marche : '.$invalidArticles->join(', '),
            ]);
        }
    }

    private function recordStatusHistory(BonCommande $bonCommande, string $ancienStatut, string $nouveauStatut, string $raison): void
    {
        HistoriqueStatutBc::create([
            'bon_commande_id' => $bonCommande->id,
            'ancien_statut' => $ancienStatut,
            'nouveau_statut' => $nouveauStatut,
            'raison' => $raison,
            'changed_by' => auth()->id(),
        ]);
    }

    private function updateArticlesPrix(BonCommande $bonCommande, Request $request): void
    {
        $validatedArticles = [];

        foreach ($request->articles as $articleData) {
            $articlePivot = BonCommandeArticle::where('id', $articleData['id'])
                ->where('bon_commande_id', $bonCommande->id)
                ->first();

            if (! $articlePivot) {
                continue;
            }

            Article::onlyNonExists()->find($articlePivot->article_id)?->update([
                'in_marche' => true,
            ]);

            $prixUnitaireHT = (float) $articleData['prix_unitaire_ht'];
            $quantite = (float) $articlePivot->quantite_commandee;
            $tauxTVA = (float) $articlePivot->taux_tva;
            $montantHT = $prixUnitaireHT * $quantite;
            $montantTVA = $montantHT * ($tauxTVA / 100);

            $articlePivot->update([
                'prix_unitaire_ht' => $prixUnitaireHT,
                'montant_ht' => $montantHT,
                'montant_tva' => $montantTVA,
                'montant_ttc' => $montantHT + $montantTVA,
            ]);

            $validatedArticles[] = $articlePivot;
        }

        if (count($validatedArticles) !== count($request->articles)) {
            Log::warning('Certains articles du marche n ont pas pu etre mis a jour', [
                'bon_commande_id' => $bonCommande->id,
                'attendus' => count($request->articles),
                'traites' => count($validatedArticles),
            ]);
        }
    }

    private function marketIndexPayload(BonCommande $marche): array
    {
        $financials = $this->financialSnapshot($marche);
        $quantities = $this->quantitySnapshot($marche);
        $alerts = $this->marketAlerts($marche, $financials);
        $isOperational = $this->marketHasOperationalData($marche);

        return [
            'id' => $marche->id,
            'reference' => $marche->reference,
            'objet' => $marche->objet,
            'description' => $marche->description,
            'statut' => $marche->statut,
            'categorie' => $marche->categorie ? [
                'id' => $marche->categorie->id,
                'nom' => $marche->categorie->nom,
                'code' => $marche->categorie->code,
                'couleur' => $marche->categorie->couleur,
            ] : null,
            'fournisseur' => $marche->fournisseur ? [
                'id' => $marche->fournisseur->id,
                'nom' => $marche->fournisseur->nom,
                'raison_sociale' => $marche->fournisseur->raison_sociale,
                'nom_affichage' => $marche->fournisseur->raison_sociale ?: $marche->fournisseur->nom,
            ] : null,
            'date_debut' => optional($marche->date_debut)->toDateString(),
            'date_fin' => optional($marche->date_fin)->toDateString(),
            'date_mise_ligne' => optional($marche->date_mise_ligne)->toDateString(),
            'date_limite_reception' => optional($marche->date_limite_reception)->toDateString(),
            'nombre_articles' => $marche->articles->count(),
            'total_ht' => $financials['total_ht'],
            'total_tva' => $financials['total_tva'],
            'total_ttc' => $financials['total_ttc'],
            'consumed_amount' => $financials['consumed_amount'],
            'remaining_amount' => $financials['remaining_amount'],
            'consumption_percent' => $financials['consumption_percent'],
            'quantite_commandee' => $quantities['ordered'],
            'quantite_livree' => $quantities['delivered'],
            'quantite_restante' => $quantities['remaining'],
            'alerts' => $alerts,
            'is_operational' => $isOperational,
            'can_delete_physical' => ! $isOperational,
        ];
    }

    private function marketDetailPayload(BonCommande $marche, $bonLivraisons): array
    {
        $base = $this->marketIndexPayload($marche);
        $financials = $this->financialSnapshot($marche);
        $quantities = $this->quantitySnapshot($marche);

        return array_merge($base, [
            'notes' => $marche->notes,
            'annule_at' => optional($marche->annule_at)->toDateString(),
            'reason_annulation' => $marche->reason_annulation,
            'created_at' => optional($marche->created_at)->format('d/m/Y'),
            'financial_sources' => $financials['sources'],
            'articles' => $marche->articles->map(function (BonCommandeArticle $ligne) use ($marche) {
                $quantiteEngagee = (float) $ligne->quantite_commandee;
                $quantiteConsommee = $marche->getQuantiteRecuePourArticle($ligne->article_id);
                $quantiteRestante = max(0, $quantiteEngagee - $quantiteConsommee);
                $seuilAlerte = $quantiteEngagee * 0.1;

                return [
                    'id' => $ligne->id,
                    'reference' => $ligne->article?->reference,
                    'designation' => $ligne->article?->designation,
                    'unite_mesure' => $ligne->article?->unite_mesure,
                    'quantite_minimale' => (float) ($ligne->quantite_minimale ?? 0),
                    'quantite_maximale' => (float) ($ligne->quantite_maximale ?? $ligne->quantite_commandee),
                    'quantite_commandee' => (float) $ligne->quantite_commandee,
                    'quantite_engagee' => round($quantiteEngagee, 2),
                    'quantite_consommee' => round($quantiteConsommee, 2),
                    'quantite_restante' => round($quantiteRestante, 2),
                    'alerte_quantite' => $quantiteRestante <= 0
                        ? 'epuise'
                        : ($quantiteRestante <= $seuilAlerte ? 'faible' : null),
                    'prix_unitaire_ht' => (float) ($ligne->prix_unitaire_ht ?? 0),
                    'taux_tva' => (float) ($ligne->taux_tva ?? 0),
                    'montant_ht' => (float) ($ligne->montant_ht ?? 0),
                    'montant_tva' => (float) ($ligne->montant_tva ?? 0),
                    'montant_ttc' => (float) ($ligne->montant_ttc ?? 0),
                ];
            }),
            'bons_commandes' => $marche->chefCommandes->map(fn (ChefCommande $commande) => [
                'id' => $commande->id,
                'numero' => $commande->numero,
                'statut' => $commande->statut,
                'date' => optional($commande->created_at)->format('d/m/Y'),
                'demandeur' => $commande->user?->name,
                'articles_count' => $commande->items->count(),
            ]),
            'livraisons' => $bonLivraisons->map(fn (BonLivraison $livraison) => [
                'id' => $livraison->id,
                'numero' => $livraison->numero,
                'statut' => $livraison->statut,
                'date' => optional($livraison->date_livraison ?: $livraison->created_at)->format('d/m/Y'),
                'total_ht' => (float) $livraison->total_ht,
                'total_tva' => (float) $livraison->total_tva,
                'total_ttc' => (float) $livraison->total_ttc,
                'reception_id' => $livraison->reception?->id,
            ]),
            'receptions' => $this->receptionPayload($marche, $bonLivraisons),
            'quantities' => $quantities,
        ]);
    }

    private function financialSnapshot(BonCommande $marche): array
    {
        $totalHt = (float) $marche->articles->sum('montant_ht');
        $totalTva = (float) $marche->articles->sum('montant_tva');
        $totalTtc = (float) $marche->articles->sum('montant_ttc');
        $decompteTtc = (float) $marche->decomptes->flatMap->items->sum('montant_ttc');
        $oldReceptionTtc = (float) $marche->bonReceptions->flatMap->lignesReception->sum(function ($ligne) {
            return (float) ($ligne->prix_total ?? 0) + (float) ($ligne->montant_tva ?? 0);
        });
        $bonLivraisonTtc = (float) $this->relatedBonLivraisons($marche)->sum(fn (BonLivraison $livraison) => (float) $livraison->total_ttc);

        $consumedAmount = $decompteTtc > 0
            ? $decompteTtc
            : max($oldReceptionTtc, $bonLivraisonTtc);

        return [
            'total_ht' => round($totalHt, 2),
            'total_tva' => round($totalTva, 2),
            'total_ttc' => round($totalTtc, 2),
            'consumed_amount' => round($consumedAmount, 2),
            'remaining_amount' => round(max($totalTtc - $consumedAmount, 0), 2),
            'consumption_percent' => $totalTtc > 0 ? min(round(($consumedAmount / $totalTtc) * 100, 1), 100) : 0,
            'sources' => [
                'decomptes_ttc' => round($decompteTtc, 2),
                'receptions_ttc' => round($oldReceptionTtc, 2),
                'livraisons_ttc' => round($bonLivraisonTtc, 2),
            ],
        ];
    }

    private function quantitySnapshot(BonCommande $marche): array
    {
        $ordered = (float) $marche->articles->sum('quantite_commandee');
        $oldDelivered = (float) $marche->bonReceptions->flatMap->lignesReception->sum('quantite_receptionnee');
        $modernDelivered = (float) $this->relatedBonLivraisons($marche)->flatMap->items->sum('quantite');
        $delivered = max($oldDelivered, $modernDelivered);

        return [
            'ordered' => round($ordered, 2),
            'delivered' => round($delivered, 2),
            'remaining' => round(max($ordered - $delivered, 0), 2),
        ];
    }

    private function marketAlerts(BonCommande $marche, array $financials): array
    {
        $alerts = [];
        $today = today();

        if ($marche->date_fin && $marche->date_fin->isPast()) {
            $alerts[] = ['type' => 'danger', 'message' => 'Marche expire'];
        } elseif ($marche->date_fin && $today->diffInDays($marche->date_fin, false) <= 30) {
            $alerts[] = ['type' => 'warning', 'message' => 'Expire dans 30 jours'];
        }

        if ($financials['consumption_percent'] >= 100) {
            $alerts[] = ['type' => 'danger', 'message' => 'Consomme a 100%'];
        } elseif ($financials['consumption_percent'] >= 90) {
            $alerts[] = ['type' => 'danger', 'message' => 'Consomme a 90%'];
        } elseif ($financials['consumption_percent'] >= 80) {
            $alerts[] = ['type' => 'warning', 'message' => 'Consomme a 80%'];
        }

        $lastDeliveryDate = $this->lastDeliveryDate($marche);
        if (in_array($marche->statut, [BonCommande::STATUT_ATTENTE_LIVRAISON, BonCommande::STATUT_LIVRE_PARTIELLEMENT], true)) {
            $days = $lastDeliveryDate
                ? $lastDeliveryDate->diffInDays($today)
                : optional($marche->updated_at ?: $marche->created_at)->diffInDays($today);

            if ($days !== null && $days >= 30) {
                $alerts[] = ['type' => 'warning', 'message' => "Sans livraison depuis {$days} jours"];
            }
        }

        return $alerts;
    }

    private function lastDeliveryDate(BonCommande $marche): ?CarbonInterface
    {
        $dates = collect();

        $marche->bonReceptions->each(function ($reception) use ($dates) {
            if ($reception->date_reception) {
                $dates->push($reception->date_reception);
            }
        });

        $this->relatedBonLivraisons($marche)->each(function (BonLivraison $livraison) use ($dates) {
            if ($livraison->date_livraison) {
                $dates->push($livraison->date_livraison);
            } elseif ($livraison->created_at) {
                $dates->push($livraison->created_at);
            }
        });

        return $dates->sortDesc()->first();
    }

    private function relatedBonLivraisons(BonCommande $marche)
    {
        if ($marche->relationLoaded('chefCommandes')) {
            $chefCommandes = $marche->chefCommandes;

            if ($chefCommandes->isEmpty()) {
                return collect();
            }

            if ($chefCommandes->every(fn (ChefCommande $commande) => $commande->relationLoaded('livraisons'))) {
                return $chefCommandes->flatMap->livraisons->values();
            }
        }

        $chefCommandeIds = $marche->relationLoaded('chefCommandes')
            ? $marche->chefCommandes->pluck('id')
            : ChefCommande::where('bon_commande_id', $marche->id)->pluck('id');

        if ($chefCommandeIds->isEmpty()) {
            return collect();
        }

        return BonLivraison::with([
                'items',
                'reception' => fn ($query) => $query
                    ->without(['media', 'bonLivraison'])
                    ->select(['id', 'bon_livraison_id', 'numero', 'created_at']),
            ])
            ->whereIn('chef_commande_id', $chefCommandeIds)
            ->get();
    }

    private function receptionPayload(BonCommande $marche, $bonLivraisons)
    {
        $modern = $bonLivraisons
            ->filter(fn (BonLivraison $livraison) => $livraison->reception)
            ->map(fn (BonLivraison $livraison) => [
                'id' => $livraison->reception->id,
                'numero' => $livraison->reception->numero,
                'source' => 'receptions',
                'date' => optional($livraison->reception->created_at)->format('d/m/Y'),
                'bon_livraison' => $livraison->numero,
                'total_ttc' => (float) $livraison->total_ttc,
            ]);

        $legacy = $marche->bonReceptions->map(fn ($reception) => [
            'id' => $reception->id,
            'numero' => $reception->numero,
            'source' => 'bon_receptions',
            'date' => optional($reception->date_reception)->format('d/m/Y'),
            'bon_livraison' => $reception->fichier_bonlivraison ? 'Fichier joint' : '-',
            'total_ttc' => (float) $reception->lignesReception->sum(fn ($ligne) => (float) ($ligne->prix_total ?? 0) + (float) ($ligne->montant_tva ?? 0)),
        ]);

        return $modern->merge($legacy)->values();
    }

    private function decomptePayload(BonCommande $marche)
    {
        return $marche->decomptes->map(fn (Decompte $decompte) => [
            'id'         => $decompte->id,
            'numero'     => $decompte->numero ?? ('DC-'.str_pad($decompte->id, 4, '0', STR_PAD_LEFT)),
            'date'       => optional($decompte->date)->toDateString(),
            'date_debut' => optional($decompte->date_debut)->toDateString(),
            'final'      => (bool) $decompte->final,
            'total_ttc'  => (float) $decompte->items->sum('montant_ttc'),
            'nb_articles' => $decompte->items->count(),
        ]);
    }

    private function marketHasOperationalData(BonCommande $marche): bool
    {
        return (bool) $marche->fournisseur_id
            || ($marche->relationLoaded('bonReceptions') ? $marche->bonReceptions->isNotEmpty() : $marche->bonReceptions()->exists())
            || ($marche->relationLoaded('decomptes') ? $marche->decomptes->isNotEmpty() : $marche->decomptes()->exists())
            || ($marche->relationLoaded('chefCommandes') ? $marche->chefCommandes->isNotEmpty() : $marche->chefCommandes()->exists())
            || $this->relatedBonLivraisons($marche)->isNotEmpty();
    }
}
