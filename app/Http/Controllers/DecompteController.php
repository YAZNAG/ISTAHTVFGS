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
use Spatie\LaravelPdf\Enums\Format;
use Spatie\LaravelPdf\Facades\Pdf;

class DecompteController extends Controller
{
    public function create(BonCommande $bonCommande)
    {
        return Inertia::modal('Achats/BonCommandes/Decompte/CreateDecompteModal', [
            'marche_id' => $bonCommande->id
        ])->baseRoute('bon-commandes.show', $bonCommande->id);
    }

    public function store(Request $request, BonCommande $bonCommande)
    {
        $request->validate([
            'date' => 'required|date',
            'is_final' => 'boolean'
        ]);

        $aleadyExitsWithDate = $bonCommande->decomptes()->whereDate('date', $request->date)->exists();
        if ($aleadyExitsWithDate) {
            throw ValidationException::withMessages([
                'date' => 'Un decompte existe deja pour cette date.'
            ]);
        }

        dd('qsdqsd');
        

        DB::transaction(function () use ($request, $bonCommande) {
            
            $decompte = $bonCommande->decomptes()->create([
                'date' => $request->date,
                'final' => $request->is_final
            ]);

            $receptions = Reception::with('bonLivraison.items.article')->whereHas('bonLivraison.chefCommande.bonCommande', function ($q) use ($decompte) {
                $q->where('bon_commandes.id', $decompte->marche_id);
            })
            ->whereDate('created_at', '<=', $decompte->date->endOfDay())
            ->get();


            $bonLivraisons = $receptions->pluck('bonLivraison')->unique('id');

            $items = $bonLivraisons
                ->flatMap(fn ($bon) => $bon->items)   // 1-dimensional collection of items
                ->groupBy('article_id')               // or groupBy('designation')
                ->map(function ($rows) {
                    $first = $rows->first();
                    return [
                        'article_id'    => $first->article_id,
                        'designation'   => $first->article->designation,
                        'unite_mesure'  => $first->article->unite_mesure,
                        'quantite'      => $rows->sum('quantite'),
                        'prix_unitaire' => number_format($first->prix_unitaire, 2, '.', ''),   // kept unchanged
                        'taux_tva'      => $first->taux_tva,
                        'montant_ht'    => number_format($rows->sum('montant_ht'), 2, '.', ''),
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
                    'montant_ttc' => $item['montant_ttc'],
                ]);
            }

        });

        return redirect()->route('bon-commandes.show', $bonCommande->id);
    }

    public function download(Decompte $decompte)
    {
        $decompte->load('items', 'marche.fournisseur');

        $items = $decompte->items->map(function ($item) {
                return [
                    'article_id'    => $item->article_id,
                    'designation'   => $item->article->designation,
                    'unite_mesure'  => $item->article->unite_mesure,
                    'quantite'      => $item->quantite,
                    'prix_unitaire' => number_format($item->prix_unitaire, 2, '.', ''),   // kept unchanged
                    'taux_tva'      => $item->taux_tva,
                    'montant_ht'    => number_format($item->montant_ht, 2, '.', ''),
                    'montant_ttc'   => number_format($item->montant_ttc, 2, '.', ''),
                ];
            });

        $previous_decompte_total = Decompte::with('items')->where('marche_id', $decompte->marche_id)->where('date', '<', $decompte->date)
            ->latest()
            ->get()
            ->sum(function ($decompte) {
                return number_format($decompte->items->sum('montant_ttc'), 2, '.', '');
            });
        
        $current_decompte_total = $decompte->items->sum('montant_ttc');

        $marche_total = number_format($decompte->marche->total_ttc, 2, '.', '');

        $travaux_termine = number_format($previous_decompte_total, 2, '.', '');

        $travaux_non_termine = number_format($marche_total - $travaux_termine, 2, '.', '');

        $decompte_total = number_format($current_decompte_total - $previous_decompte_total, 2, '.', '');

        $fileName = "decompte-{$decompte->marche->reference}-{$decompte->date}.pdf";
        return Pdf::view('pdf.decompte', [
            'items' => $items,
            "travaux_termine" => $travaux_termine,
            "travaux_non_termine" => $travaux_non_termine,
            "decompte_total" => $decompte_total,
            "marche" => $decompte->marche
        ])->format(Format::A4)
            ->headerView('pdf.H')
            ->footerView('pdf.F')
            ->margins(45, 5, 35, 5)
            ->download($fileName)
            ;
    }
}
