<?php

namespace App\Http\Controllers;

use App\Models\BonCommande;
use App\Models\BonLivraison;
use App\Models\Decompte;
use App\Models\Reception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

class DecompteController extends Controller
{
    public function create(BonCommande $bonCommande)
    {
        return Inertia::modal('Achats/BonCommandes/Decompte/CreateDecompteModal', [
            'marche_id'          => $bonCommande->id,
            'default_categorie_id' => $bonCommande->categorie_id,
            'categories'         => \App\Models\Categorie::actives()->orderBy('nom')->get(['id', 'nom']),
        ])->baseRoute('bon-commandes.show', $bonCommande->id);
    }

    public function store(Request $request, BonCommande $bonCommande)
    {
        $request->validate([
            'date' => 'required|date',
            'date_debut' => 'nullable|date|before_or_equal:date',
            'categorie_id' => 'nullable|exists:categories,id',
            'is_final' => 'boolean'
        ]);

        $aleadyExitsWithDate = $bonCommande->decomptes()->whereDate('date', $request->date)->exists();
        if ($aleadyExitsWithDate) {
            throw ValidationException::withMessages([
                'date' => 'Un decompte existe deja pour cette date.'
            ]);
        }


        DB::transaction(function () use ($request, $bonCommande) {

            $decompte = $bonCommande->decomptes()->create([
                'date' => $request->date,
                'date_debut' => $request->date_debut,
                'categorie_id' => $request->categorie_id,
                'final' => $request->is_final
            ]);

            $receptions = Reception::with('bonLivraison.items.article.categorie', 'bonLivraison.fournisseur')
                ->whereHas('bonLivraison.chefCommande.bonCommande', function ($q) use ($decompte) {
                    $q->where('bon_commandes.id', $decompte->marche_id);
                })
                ->when($decompte->date_debut, function ($q) use ($decompte) {
                    $q->whereDate('created_at', '>=', $decompte->date_debut);
                })
                ->whereDate('created_at', '<=', $decompte->date->endOfDay())
                ->get();


            $bonLivraisons = $receptions->pluck('bonLivraison')->unique('id');

            $items = $bonLivraisons
                ->flatMap(fn ($bon) => $bon->items->map(function ($item) use ($bon) {
                    $item->setRelation('bonLivraison', $bon);
                    return $item;
                }))
                ->when($decompte->categorie_id, function ($collection) use ($decompte) {
                    return $collection->filter(fn ($item) => $item->article?->categorie_id === $decompte->categorie_id);
                })
                ->groupBy('article_id')
                ->map(function ($rows) {
                    $first = $rows->first();
                    $montantHt = $rows->sum('montant_ht');
                    $montantTva = $rows->sum('montant_tva');

                    return [
                        'article_id'    => $first->article_id,
                        'designation'   => $first->article->designation,
                        'unite_mesure'  => $first->article->unite_mesure,
                        'quantite'      => $rows->sum('quantite'),
                        'prix_unitaire' => number_format($first->prix_unitaire, 2, '.', ''),
                        'taux_tva'      => $first->taux_tva,
                        'montant_ht'    => number_format($montantHt, 2, '.', ''),
                        'montant_tva'   => number_format($montantTva, 2, '.', ''),
                        'montant_ttc'   => number_format($rows->sum('montant_ttc'), 2, '.', ''),
                    ];
                })
                ->values();


            foreach ($items as $item) {
                $decompte->items()->create([
                    'article_id' => $item['article_id'],
                    'quantite' => $item['quantite'],
                    'prix_unitaire' => $item['prix_unitaire'],
                    'taux_tva' => $item['taux_tva'],
                    'montant_ht' => $item['montant_ht'],
                    'montant_tva' => $item['montant_tva'],
                    'montant_ttc' => $item['montant_ttc'],
                ]);
            }

        });

        return redirect()->route('bon-commandes.show', $bonCommande->id);
    }

    public function exportExcel(Decompte $decompte)
    {
        $decompte->load('items.article.categorie', 'marche');

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\DecompteExport($decompte),
            "decompte-{$decompte->marche->reference}-{$decompte->date->format('Y-m-d')}.xlsx"
        );
    }

    public function destroy(Decompte $decompte)
    {
        $marcheId = $decompte->marche_id;
        $decompte->items()->delete();
        $decompte->delete();
        return redirect()->route('bon-commandes.show', $marcheId);
    }

    public function download(Decompte $decompte)
    {
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', '120');

        $decompte->load([
            'items.article:id,designation,unite_mesure',
            'marche.fournisseur:id,nom,raison_sociale,adresse,ville,telephone,ice,tp,rc,if,cb',
            'marche.categorie:id,nom',
        ]);

        $items = $decompte->items->map(fn ($item) => [
            'article_id'    => $item->article_id,
            'designation'   => $item->article->designation,
            'unite_mesure'  => $item->article->unite_mesure,
            'quantite'      => $item->quantite,
            'prix_unitaire' => number_format((float) $item->prix_unitaire, 2, '.', ''),
            'taux_tva'      => $item->taux_tva,
            'montant_ht'    => number_format((float) $item->montant_ht, 2, '.', ''),
            'montant_tva'   => number_format((float) $item->montant_tva, 2, '.', ''),
            'montant_ttc'   => number_format((float) $item->montant_ttc, 2, '.', ''),
        ]);

        $previous_decompte_total = \App\Models\DecompteItem::whereHas('decompte', fn ($q) => $q
            ->where('marche_id', $decompte->marche_id)
            ->where('date', '<', $decompte->date)
        )->sum('montant_ttc');

        $current_decompte_total  = $decompte->items->sum('montant_ttc');
        $marche_total            = (float) $decompte->marche->total_ttc;
        $travaux_termine         = number_format((float) $previous_decompte_total, 2, '.', '');
        $travaux_non_termine     = number_format($marche_total - (float) $previous_decompte_total, 2, '.', '');
        $decompte_total          = number_format($current_decompte_total - (float) $previous_decompte_total, 2, '.', '');

        $cleanRef  = preg_replace('/[\/\\\\]/', '-', $decompte->marche->reference);
        $fileName  = "decompte-{$cleanRef}-{$decompte->date->format('Y-m-d')}.pdf";

        $imgPath  = public_path('images/pdf-header.jpg');
        $imgData  = file_exists($imgPath) ? @file_get_contents($imgPath) : false;
        $pdfHeaderSrc = $imgData !== false
            ? 'data:image/jpeg;base64,' . base64_encode($imgData)
            : null;

        return Pdf::loadView('pdf.decompte', [
            'items'              => $items,
            'marche'             => $decompte->marche,
            'date'               => $decompte->date,
            'date_debut'         => $decompte->date_debut,
            'decompte_final'     => (bool) $decompte->final,
            'travaux_termine'    => $travaux_termine,
            'travaux_non_termine'=> $travaux_non_termine,
            'decompte_total'     => $decompte_total,
            'pdfHeaderSrc'       => $pdfHeaderSrc,
        ])
        ->setPaper('a4', 'portrait')
        ->download($fileName);
    }
}
