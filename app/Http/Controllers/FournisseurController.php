<?php

namespace App\Http\Controllers;

use App\Exports\FournisseursExport;
use App\Models\BonCommande;
use App\Models\BonLivraison;
use App\Models\BonReception;
use App\Models\Decompte;
use App\Models\Fournisseur;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class FournisseurController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:list_fournisseurs', only: ['index']),
            new Middleware('permission:show_fournisseurs', only: ['show']),
            new Middleware('permission:create_fournisseurs', only: ['store']),
            new Middleware('permission:edit_fournisseurs', only: ['update', 'toggleStatut']),
            new Middleware('permission:delete_fournisseurs', only: ['destroy']),
            new Middleware('permission:export_fournisseurs', only: ['export', 'exportPdf', 'exportExcel']),
        ];
    }

    public function index(Request $request)
    {
        $fournisseurs = $this->filteredFournisseurs($request)
            ->select('fournisseurs.*')
            ->selectSub(function ($query) {
                $query->from('bon_commandes')
                    ->join('bon_commande_articles', 'bon_commande_articles.bon_commande_id', '=', 'bon_commandes.id')
                    ->whereColumn('bon_commandes.fournisseur_id', 'fournisseurs.id')
                    ->selectRaw('COALESCE(SUM(bon_commande_articles.montant_ttc), 0)');
            }, 'montant_total_attribue')
            ->withCount([
                'bonCommandes as marches_count',
                'bonLivraisons as livraisons_count',
                'bonReceptions as receptions_count',
                'decomptes as decomptes_count',
            ])
            ->with('media')
            ->latest()
            ->paginate(12)
            ->withQueryString()
            ->through(fn (Fournisseur $fournisseur) => $this->indexPayload($fournisseur));

        return inertia('Achats/Fournisseurs/Index', [
            'fournisseurs' => $fournisseurs,
            'stats' => $this->stats(),
            'filters' => $request->only(['search', 'statut']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validatePayload($request);
        $validated['est_actif'] = $request->boolean('est_actif', true);
        unset($validated['logo']);

        DB::transaction(function () use ($request, $validated) {
            $fournisseur = Fournisseur::create($validated);

            if ($request->hasFile('logo')) {
                $fournisseur->addMediaFromRequest('logo')->toMediaCollection('logo');
            }
        });

        return redirect()
            ->route('fournisseurs.index')
            ->with('success', 'Fournisseur cree avec succes.');
    }

    public function show(Fournisseur $fournisseur)
    {
        $fournisseur->loadCount([
            'bonCommandes as marches_count',
            'bonLivraisons as livraisons_count',
            'bonReceptions as receptions_count',
            'decomptes as decomptes_count',
        ]);

        return inertia('Achats/Fournisseurs/Show', [
            'fournisseur' => $this->showPayload($fournisseur),
        ]);
    }

    public function update(Request $request, Fournisseur $fournisseur)
    {
        $validated = $this->validatePayload($request);
        $validated['est_actif'] = $request->boolean('est_actif', $fournisseur->est_actif);
        unset($validated['logo']);

        DB::transaction(function () use ($request, $fournisseur, $validated) {
            $fournisseur->update($validated);

            if ($request->hasFile('logo')) {
                $fournisseur->addMediaFromRequest('logo')->toMediaCollection('logo');
            }
        });

        return redirect()
            ->route('fournisseurs.index')
            ->with('success', 'Fournisseur modifie avec succes.');
    }

    public function destroy(Fournisseur $fournisseur)
    {
        $fournisseur->loadCount([
            'bonCommandes as marches_count',
            'bonLivraisons as livraisons_count',
            'bonReceptions as receptions_count',
            'decomptes as decomptes_count',
        ]);

        if ($this->isUsed($fournisseur)) {
            $fournisseur->update(['est_actif' => false]);

            return redirect()
                ->back()
                ->with('warning', 'Ce fournisseur est deja lie a des marches ou documents. Il a ete desactive au lieu d etre supprime definitivement.');
        }

        $name = $fournisseur->nom_affichage;
        $fournisseur->clearMediaCollection('logo');
        $fournisseur->delete();

        return redirect()
            ->route('fournisseurs.index')
            ->with('success', "Le fournisseur {$name} a ete supprime.");
    }

    public function toggleStatut(Fournisseur $fournisseur)
    {
        $fournisseur->update([
            'est_actif' => ! $fournisseur->est_actif,
        ]);

        return redirect()
            ->back()
            ->with('success', $fournisseur->est_actif ? 'Fournisseur active.' : 'Fournisseur desactive.');
    }

    public function export(Request $request)
    {
        return $this->exportPdf($request);
    }

    public function exportPdf(Request $request)
    {
        $fournisseurs = $this->exportQuery($request)->get();
        $pdf = $this->buildFournisseursPdf($fournisseurs);

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="fournisseurs.pdf"',
        ]);
    }

    public function exportExcel(Request $request)
    {
        return Excel::download(new FournisseursExport($request->only(['search', 'statut'])), 'fournisseurs.xlsx');
    }

    private function validatePayload(Request $request): array
    {
        return $request->validate([
            'nom' => 'required|string|max:255',
            'raison_sociale' => 'nullable|string|max:255',
            'contact' => 'nullable|string|max:255',
            'telephone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'adresse' => 'nullable|string|max:500',
            'ville' => 'nullable|string|max:100',
            'ice' => 'nullable|string|max:50',
            'tp' => 'nullable|string|max:50',
            'rc' => 'nullable|string|max:50',
            'if' => 'nullable|string|max:50',
            'cb' => 'nullable|string|max:80',
            'cnss' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:2000',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'est_actif' => 'nullable|boolean',
        ]);
    }

    private function filteredFournisseurs(Request $request): Builder
    {
        return Fournisseur::query()
            ->when($request->filled('search'), function (Builder $query) use ($request) {
                $search = $request->input('search');

                $query->where(function (Builder $query) use ($search) {
                    $query->where('nom', 'like', "%{$search}%")
                        ->orWhere('raison_sociale', 'like', "%{$search}%")
                        ->orWhere('contact', 'like', "%{$search}%")
                        ->orWhere('telephone', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('ville', 'like', "%{$search}%")
                        ->orWhere('ice', 'like', "%{$search}%");
                });
            })
            ->when($request->input('statut') === 'actifs', fn (Builder $query) => $query->where('est_actif', true))
            ->when($request->input('statut') === 'inactifs', fn (Builder $query) => $query->where('est_actif', false));
    }

    private function exportQuery(Request $request): Builder
    {
        return $this->filteredFournisseurs($request)
            ->select('fournisseurs.*')
            ->withCount(['bonCommandes as marches_count'])
            ->orderBy('raison_sociale')
            ->orderBy('nom');
    }

    private function stats(): array
    {
        $totalAttribue = BonCommande::query()
            ->join('bon_commande_articles', 'bon_commande_articles.bon_commande_id', '=', 'bon_commandes.id')
            ->whereNotNull('bon_commandes.fournisseur_id')
            ->sum('bon_commande_articles.montant_ttc');

        return [
            'total' => Fournisseur::count(),
            'actifs' => Fournisseur::where('est_actif', true)->count(),
            'inactifs' => Fournisseur::where('est_actif', false)->count(),
            'marches' => BonCommande::whereNotNull('fournisseur_id')->count(),
            'montant_total_attribue' => round((float) $totalAttribue, 2),
        ];
    }

    private function indexPayload(Fournisseur $fournisseur): array
    {
        $isUsed = $this->isUsed($fournisseur);

        return [
            'id' => $fournisseur->id,
            'nom' => $fournisseur->nom,
            'raison_sociale' => $fournisseur->raison_sociale,
            'nom_affichage' => $fournisseur->nom_affichage,
            'contact' => $fournisseur->contact,
            'telephone' => $fournisseur->telephone,
            'email' => $fournisseur->email,
            'adresse' => $fournisseur->adresse,
            'ville' => $fournisseur->ville,
            'ice' => $fournisseur->ice,
            'tp' => $fournisseur->tp,
            'rc' => $fournisseur->rc,
            'if' => $fournisseur->if,
            'cb' => $fournisseur->cb,
            'cnss' => $fournisseur->cnss,
            'notes' => $fournisseur->notes,
            'est_actif' => (bool) $fournisseur->est_actif,
            'logo_url' => $fournisseur->getFirstMediaUrl('logo'),
            'marches_count' => (int) ($fournisseur->marches_count ?? 0),
            'livraisons_count' => (int) ($fournisseur->livraisons_count ?? 0),
            'receptions_count' => (int) ($fournisseur->receptions_count ?? 0),
            'decomptes_count' => (int) ($fournisseur->decomptes_count ?? 0),
            'montant_total_attribue' => (float) ($fournisseur->montant_total_attribue ?? 0),
            'is_used' => $isUsed,
            'can_delete_physical' => ! $isUsed,
            'created_at' => optional($fournisseur->created_at)->format('d/m/Y'),
        ];
    }

    private function showPayload(Fournisseur $fournisseur): array
    {
        $marches = $fournisseur->bonCommandes()
            ->withCount('articles')
            ->withSum('articles as montant_ttc_sum', 'montant_ttc')
            ->with(['categorie:id,nom,code,couleur'])
            ->latest()
            ->limit(30)
            ->get();

        $livraisons = $fournisseur->bonLivraisons()
            ->with(['items:id,bon_livraison_id,quantite,prix_unitaire,taux_tva', 'reception:id,bon_livraison_id,numero,created_at'])
            ->latest()
            ->limit(30)
            ->get();

        $legacyReceptions = $fournisseur->bonReceptions()
            ->with('lignesReception:id,bon_reception_id,prix_total,montant_tva')
            ->latest()
            ->limit(30)
            ->get();

        $modernReceptions = BonLivraison::query()
            ->where('fournisseur_id', $fournisseur->id)
            ->whereHas('reception')
            ->with([
                'items:id,bon_livraison_id,quantite,prix_unitaire,taux_tva',
                'reception:id,bon_livraison_id,numero,created_at',
            ])
            ->latest()
            ->limit(30)
            ->get();

        $decomptes = Decompte::query()
            ->whereHas('marche', fn (Builder $query) => $query->where('fournisseur_id', $fournisseur->id))
            ->with(['marche:id,reference,objet,fournisseur_id', 'items:id,decompte_id,montant_ttc'])
            ->latest('date')
            ->limit(30)
            ->get();

        $base = $this->indexPayload($fournisseur);
        $logo = $fournisseur->getFirstMedia('logo');

        return array_merge($base, [
            'montant_total_attribue' => (float) $marches->sum('montant_ttc_sum'),
            'created_at' => optional($fournisseur->created_at)->format('d/m/Y'),
            'updated_at' => optional($fournisseur->updated_at)->format('d/m/Y'),
            'marches' => $marches->map(fn (BonCommande $marche) => [
                'id' => $marche->id,
                'reference' => $marche->reference,
                'objet' => $marche->objet,
                'statut' => $marche->statut,
                'categorie' => $marche->categorie?->nom,
                'date_debut' => optional($marche->date_debut)->toDateString(),
                'date_fin' => optional($marche->date_fin)->toDateString(),
                'articles_count' => (int) $marche->articles_count,
                'montant_ttc' => (float) ($marche->montant_ttc_sum ?? 0),
            ]),
            'livraisons' => $livraisons->map(fn (BonLivraison $livraison) => [
                'id' => $livraison->id,
                'numero' => $livraison->numero,
                'statut' => $livraison->statut,
                'date' => optional($livraison->date_livraison ?: $livraison->created_at)->format('d/m/Y'),
                'total_ht' => (float) $livraison->total_ht,
                'total_tva' => (float) $livraison->total_tva,
                'total_ttc' => (float) $livraison->total_ttc,
                'reception_id' => $livraison->reception?->id,
            ]),
            'receptions' => $modernReceptions->map(fn (BonLivraison $livraison) => [
                'id' => $livraison->reception?->id,
                'numero' => $livraison->reception?->numero,
                'source' => 'receptions',
                'date' => optional($livraison->reception?->created_at)->format('d/m/Y'),
                'bon_livraison' => $livraison->numero,
                'total_ttc' => (float) $livraison->total_ttc,
            ])->merge($legacyReceptions->map(fn (BonReception $reception) => [
                'id' => $reception->id,
                'numero' => $reception->numero,
                'source' => 'bon_receptions',
                'date' => optional($reception->date_reception)->format('d/m/Y'),
                'bon_livraison' => $reception->fichier_bonlivraison ? 'Fichier joint' : '-',
                'total_ttc' => (float) $reception->lignesReception->sum(fn ($ligne) => (float) ($ligne->prix_total ?? 0) + (float) ($ligne->montant_tva ?? 0)),
            ]))->values(),
            'decomptes' => $decomptes->map(fn (Decompte $decompte) => [
                'id' => $decompte->id,
                'date' => optional($decompte->date)->toDateString(),
                'final' => (bool) $decompte->final,
                'marche_reference' => $decompte->marche?->reference,
                'marche_objet' => $decompte->marche?->objet,
                'total_ttc' => (float) $decompte->items->sum('montant_ttc'),
            ]),
            'documents' => collect($logo ? [[
                'id' => $logo->id,
                'nom' => $logo->file_name,
                'type' => 'Logo',
                'url' => $logo->getUrl(),
                'date' => optional($logo->created_at)->format('d/m/Y'),
            ]] : []),
        ]);
    }

    private function isUsed(Fournisseur $fournisseur): bool
    {
        return ((int) ($fournisseur->marches_count ?? 0)
            + (int) ($fournisseur->livraisons_count ?? 0)
            + (int) ($fournisseur->receptions_count ?? 0)
            + (int) ($fournisseur->decomptes_count ?? 0)) > 0;
    }

    private function buildFournisseursPdf($fournisseurs): string
    {
        $objects = [
            1 => '<< /Type /Catalog /Pages 2 0 R >>',
            2 => null,
            3 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>',
        ];
        $pageIds = [];
        $nextId = 4;

        foreach ($fournisseurs->chunk(18) as $pageIndex => $chunk) {
            $content = $this->pdfText(40, 560, 15, 'Liste des fournisseurs')
                .$this->pdfText(640, 560, 8, 'Genere le '.now()->format('d/m/Y H:i'))
                .$this->pdfText(40, 535, 8, 'Nom | Raison sociale | Contact | Telephone | Email | Ville | ICE | Statut | Marches');

            $y = 515;
            foreach ($chunk as $fournisseur) {
                $lineOne = implode(' | ', [
                    $fournisseur->nom,
                    $fournisseur->raison_sociale ?: '-',
                    $fournisseur->contact ?: '-',
                    $fournisseur->telephone ?: '-',
                    $fournisseur->email ?: '-',
                ]);
                $lineTwo = implode(' | ', [
                    'Ville: '.($fournisseur->ville ?: '-'),
                    'ICE: '.($fournisseur->ice ?: '-'),
                    'Statut: '.($fournisseur->est_actif ? 'Actif' : 'Inactif'),
                    'Marches: '.((int) ($fournisseur->marches_count ?? 0)),
                ]);

                $content .= $this->pdfText(40, $y, 8, Str::limit($lineOne, 155, '...'));
                $content .= $this->pdfText(40, $y - 12, 8, Str::limit($lineTwo, 155, '...'));
                $y -= 28;
            }

            $content .= $this->pdfText(760, 24, 8, 'Page '.($pageIndex + 1));

            $contentId = $nextId++;
            $pageId = $nextId++;
            $objects[$contentId] = "<< /Length ".strlen($content)." >>\nstream\n{$content}endstream";
            $objects[$pageId] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 842 595] /Resources << /Font << /F1 3 0 R >> >> /Contents {$contentId} 0 R >>";
            $pageIds[] = "{$pageId} 0 R";
        }

        if ($pageIds === []) {
            $content = $this->pdfText(40, 560, 15, 'Liste des fournisseurs')
                .$this->pdfText(40, 530, 10, 'Aucun fournisseur trouve.');
            $contentId = $nextId++;
            $pageId = $nextId++;
            $objects[$contentId] = "<< /Length ".strlen($content)." >>\nstream\n{$content}endstream";
            $objects[$pageId] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 842 595] /Resources << /Font << /F1 3 0 R >> >> /Contents {$contentId} 0 R >>";
            $pageIds[] = "{$pageId} 0 R";
        }

        $objects[2] = '<< /Type /Pages /Kids ['.implode(' ', $pageIds).'] /Count '.count($pageIds).' >>';

        ksort($objects);

        $pdf = "%PDF-1.4\n";
        $offsets = [0];

        foreach ($objects as $id => $object) {
            $offsets[$id] = strlen($pdf);
            $pdf .= "{$id} 0 obj\n{$object}\nendobj\n";
        }

        $xrefPosition = strlen($pdf);
        $pdf .= "xref\n0 ".(count($objects) + 1)."\n";
        $pdf .= "0000000000 65535 f \n";

        for ($id = 1; $id <= count($objects); $id++) {
            $pdf .= str_pad((string) $offsets[$id], 10, '0', STR_PAD_LEFT)." 00000 n \n";
        }

        $pdf .= "trailer\n<< /Size ".(count($objects) + 1)." /Root 1 0 R >>\n";
        $pdf .= "startxref\n{$xrefPosition}\n%%EOF";

        return $pdf;
    }

    private function pdfText(int $x, int $y, int $size, string $text): string
    {
        return "BT /F1 {$size} Tf {$x} {$y} Td (".$this->pdfEscape($text).") Tj ET\n";
    }

    private function pdfEscape(string $text): string
    {
        $text = Str::ascii($text);
        $text = str_replace(["\\", '(', ')', "\r", "\n"], ["\\\\", "\\(", "\\)", ' ', ' '], $text);

        return $text;
    }
}
