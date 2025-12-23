<?php

namespace App\Http\Controllers;

use App\Models\BonCommande;
use App\Models\Decompte;
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
}
