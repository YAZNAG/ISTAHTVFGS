<?php

namespace App\Http\Controllers;

use App\Enums\Meal;
use App\Http\Requests\StoreMenuRequest;
use App\Http\Resources\CreateMenuCollectiviteResource;
use App\Http\Resources\EditMenuCollectiviteResource;
use App\Models\FicheTechnique;
use App\Models\MenuCollectivite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class MenuCollectiviteController extends Controller
{
    public function index(Request $request)
    {
        $query = MenuCollectivite::query();

        if ($request->filled('date')) {
            $query->whereDate('date', $request->date);
        }

        // Recherche
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('responsable', 'like', "%{$search}%");
        }
        
        $menus = $query->paginate(10);
        
        return Inertia::render('MenusCollectivites/Index', [
            'menus' => $menus,
            'filters' => $request->only(['date', 'search'])
        ]);
    }

    public function create()
    {
        $fiches = FicheTechnique::whereNull('menu_collectivite_id')
            ->with('repas', 'plat')
            ->collectivite()
            ->where('created_by', auth()->user()->id)
            ->get();

        return Inertia::render('MenusCollectivites/Create', [
            'fiches' => CreateMenuCollectiviteResource::collection($fiches),
        ]);
    }

    public function store(StoreMenuRequest $request){


        DB::transaction(function () use ($request) {

            $menu = MenuCollectivite::create([
                'date' => $request->date,
                'responsable' => $request->responsable,
                'effectif' => $request->effectif,
            ]);

            $menus = $request->menus;

            $petit_dejeuner = collect($menus['petit_dejeuner'])->filter(fn ($i) => !is_null($i)); 
            $dejeuner = collect($menus['dejeuner'])->filter(fn ($i) => !is_null($i));
            $diner = collect($menus['diner'])->filter(fn ($i) => !is_null($i)); 
            
            FicheTechnique::whereIn('id', $petit_dejeuner)->update(['menu_collectivite_id' => $menu->id, 'meal' => Meal::PetitDejeuner]);
            FicheTechnique::whereIn('id', $dejeuner)->update(['menu_collectivite_id' => $menu->id, 'meal' => Meal::Dejeuner]);
            FicheTechnique::whereIn('id', $diner)->update(['menu_collectivite_id' => $menu->id, 'meal' => Meal::Diner]);

        });
        
        return redirect()->route('menus.index')
                ->with('success', 'Menu collectivité créé avec succès');
    }


    public function edit(MenuCollectivite $menu)
    {
        $menu->loadMissing('fiches.repas');

        $fiches = FicheTechnique::with('repas', 'plat')
            ->collectivite()
            ->where('created_by', auth()->user()->id)
            ->where(function ($q) use ($menu) {
                $q->whereNull('menu_collectivite_id')
                ->orWhere('menu_collectivite_id', $menu->id);
            })
            ->get();

        return Inertia::render('MenusCollectivites/Edit', [
            'menu'   => EditMenuCollectiviteResource::make($menu),
            'fiches' => CreateMenuCollectiviteResource::collection($fiches),
        ]);
    }

    public function update(StoreMenuRequest $request, MenuCollectivite $menu)
    {
        DB::transaction(function () use ($request, $menu) {

            $menu->update([
                'date' => $request->date,
                'responsable' => $request->responsable,
                'effectif' => $request->effectif,
            ]);

            $menus = $request->menus;

            $petit_dejeuner = collect($menus['petit_dejeuner'])->filter(fn ($i) => !is_null($i)); 
            $dejeuner = collect($menus['dejeuner'])->filter(fn ($i) => !is_null($i));
            $diner = collect($menus['diner'])->filter(fn ($i) => !is_null($i)); 

            FicheTechnique::where('menu_collectivite_id', $menu->id)->update(['menu_collectivite_id' => null, 'meal' => null]);
            
            FicheTechnique::whereIn('id', $petit_dejeuner)->update(['menu_collectivite_id' => $menu->id, 'meal' => Meal::PetitDejeuner]);
            FicheTechnique::whereIn('id', $dejeuner)->update(['menu_collectivite_id' => $menu->id, 'meal' => Meal::Dejeuner]);
            FicheTechnique::whereIn('id', $diner)->update(['menu_collectivite_id' => $menu->id, 'meal' => Meal::Diner]);

        });
        
        return redirect()->route('menus.index')
                ->with('success', 'Menu collectivité mis à jour avec succès');
    }
}
