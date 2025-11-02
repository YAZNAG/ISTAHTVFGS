<?php

namespace App\Http\Controllers;

use App\Models\BonLivraison;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BonLivraisonController extends Controller
{
    public function index()
    {
        $bonLivraisons = BonLivraison::paginate(10);
        $pendingLivraisons = BonLivraison::pending()->get();
        return Inertia::render('BonLivraisons/Index', [
            'bonLivraisons' => $bonLivraisons,
            'pendingLivraisons' => $pendingLivraisons
        ]);
    }


    public function create()
    {
        // return Inertia::modal('BonLivraisons/Create');
    }
}
