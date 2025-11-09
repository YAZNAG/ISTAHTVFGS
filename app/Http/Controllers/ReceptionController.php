<?php

namespace App\Http\Controllers;

use App\Http\Resources\IndexReceptionResource;
use App\Http\Resources\ShowReceptionResource;
use App\Models\BonLivraison;
use App\Models\Reception;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReceptionController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;

        $receptions = Reception::
            when($search, function ($query, $search) {
                return $query->where('numero', 'like', '%' . $search . '%');
            })
            ->paginate(10)->withQueryString();

        return Inertia::render('Receptions/Index', [
            'bonReceptions' => IndexReceptionResource::collection($receptions),
            'filtres' => [
                'search' => $search,
            ]
        ]);
    }

    public function create()
    {
        $bonLivraisons = BonLivraison::livree()->whereDoesntHave('reception')->get(['id', 'numero']);

        return Inertia::modal('Receptions/Create', [
            'bonLivraisons' => $bonLivraisons
        ])->baseRoute('bon-receptions.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'bon_livraison_id' => 'required|exists:bon_livraisons,id',
            'bon' => 'required|file',
        ]);

        $reception = Reception::create([
            'bon_livraison_id' => $request->bon_livraison_id,
            'numero' => Reception::genererNumero(),
        ]);

        $reception->addMediaFromRequest('bon')->usingName("Bon de réception {$reception->numero}")->toMediaCollection('bon');

        # TODO: add mouvement stock 

        return redirect()->back()->with('success', 'Bon de reception ajouté avec succès');
    }

    public function show(Request $request, Reception $reception)
    {
        
        return Inertia::render('Receptions/ShowDetails', [
            'bonReception' => ShowReceptionResource::make($reception)
        ]);
    }

    public function destroy(Request $request, Reception $reception)
    {
        # TODO: delete reception and the related mouvement stock
        $reception->clearMediaCollection('bon');
        
        $reception->delete();

        return redirect()->back()->with('success', 'Bon de reception supprimé avec succès');
    }
}
