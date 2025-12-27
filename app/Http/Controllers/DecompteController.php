<?php

namespace App\Http\Controllers;

use App\Models\BonCommande;
use App\Models\BonLivraison;
use App\Models\Decompte;
use App\Models\Reception;
use Illuminate\Http\Request;
use Inertia\Inertia;

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
        
        $bonCommande->decomptes()->create([
            'date' => $request->date,
            'final' => $request->is_final
        ]);

    }

    public function download(Decompte $decompte)
    {
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
        

        // return response()->json($items);

        return view('pdf.decompte', [
            'items' => $items
        ]);
    }
}
