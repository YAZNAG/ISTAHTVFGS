<?php

namespace App\Http\Controllers;

use App\Models\Categorie;
use App\Models\CategoriePrincipale;
use App\Models\NaturePrestation;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;

class CategorieController extends Controller implements HasMiddleware
{

    public static function middleware(): array
    {
        return [
            new Middleware('permission:list_categories', only: ['index']),
            new Middleware('permission:create_categories', only: ['store']),
            new Middleware('permission:edit_categories', only: ['edit', 'update']),

        ];
    }
    
    public function index()
    {
        $categories = Categorie::all();

        return Inertia::render('Categories/Index', [
            'categories' => $categories,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|max:255',
            'code' => 'required|max:20|unique:categories,code',
            'description' => 'nullable|max:255',
            'est_actif' => 'boolean',
        ]);

        $nature = NaturePrestation::first();
        $categoryPrincipal = CategoriePrincipale::first();

        Categorie::create([
            'nom' => $request->nom, 
            'code' => $request->code, 
            'description' => $request->description, 
            'categorie_principale_id' => $categoryPrincipal->id, 
            'nature_prestation_id' => $nature->id, 
            'est_actif' => $request->boolean('est_actif'),
        ]);
        
        return redirect()->back()->with('success', 'Catégorie créée avec succès.');
    }

    public function edit(Categorie $categorie)
    {

        return Inertia::modal('Categories/EditCategorieModal', [
            'categorie' => $categorie,
        ])->baseRoute('categories.index');
    }

    public function update(Request $request, Categorie $categorie)
    {
        $request->validate([
            'nom' => 'required|max:255',
            'code' => 'required|max:20|unique:categories,code',
            'description' => 'nullable|max:255',
            'est_actif' => 'boolean',
        ]);


        $categorie->update([
            'nom' => $request->nom, 
            'code' => $request->code, 
            'description' => $request->description, 
            'est_actif' => $request->boolean('est_actif'),
        ]);
        
        return redirect()->back()->with('success', 'Catégorie modifiée avec succès.');
    }
}
