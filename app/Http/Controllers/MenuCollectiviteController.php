<?php

namespace App\Http\Controllers;

use App\Enums\Meal;
use App\Http\Requests\StoreMenuRequest;
use App\Http\Resources\CreateMenuCollectiviteResource;
use App\Http\Resources\EditMenuCollectiviteResource;
use App\Http\Resources\ExportMenuCollectiviteResource;
use App\Models\FicheTechnique;
use App\Models\MenuCollectivite;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Spatie\LaravelPdf\Enums\Format;
use Spatie\LaravelPdf\Enums\Orientation;
use Spatie\LaravelPdf\Facades\Pdf;

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

    public function download(MenuCollectivite $menu)
    {
        $menuId = $menu->id;
        $fiches = FicheTechnique::with([
            'repas',
            'plat',
            'ingredients.article.marcheCategory'
        ])
            ->where('menu_collectivite_id', $menuId)
            ->collectivite()
            ->get();

        $repas = [
            'petit-dejeuner' => [
                'hors_doeuvre' => $fiches->where('meal', Meal::PetitDejeuner)->where('repas.nom', 'hors d\'oeuvres')->first()->plat->nom,
                'plat' => $fiches->where('meal', Meal::PetitDejeuner)->where('repas.nom', 'plats')->first()->plat->nom,
                'dessert' => $fiches->where('meal', Meal::PetitDejeuner)->where('repas.nom', 'desserts')->first()->plat->nom,
                'plat_special' => $fiches->where('meal', Meal::PetitDejeuner)->where('repas.nom', 'plats spéciaux')->first()?->plat->nom ?? '-----',
            ],
            'dejeuner' => [
                'hors_doeuvre' => $fiches->where('meal', Meal::Dejeuner)->where('repas.nom', 'hors d\'oeuvres')->first()->plat->nom,
                'plat' => $fiches->where('meal', Meal::Dejeuner)->where('repas.nom', 'plats')->first()->plat->nom,
                'dessert' => $fiches->where('meal', Meal::Dejeuner)->where('repas.nom', 'desserts')->first()->plat->nom,
                'plat_special' => $fiches->where('meal', Meal::Dejeuner)->where('repas.nom', 'plats spéciaux')->first()?->plat->nom ?? '-----',
            ],
            'diner' => [
                'hors_doeuvre' => $fiches->where('meal', Meal::Diner)->where('repas.nom', 'hors d\'oeuvres')->first()->plat->nom,
                'plat' => $fiches->where('meal', Meal::Diner)->where('repas.nom', 'plats')->first()->plat->nom,
                'dessert' => $fiches->where('meal', Meal::Diner)->where('repas.nom', 'desserts')->first()->plat->nom,
                'plat_special' => $fiches->where('meal', Meal::Diner)->where('repas.nom', 'plats spéciaux')->first()?->plat->nom ?? '-----',
            ]
        ];


        $data = $fiches->flatMap(function ($fiche) {
            return $fiche->ingredients->map(function ($ingredient) use ($fiche) {
                return [
                    'marche_category' => $ingredient->article?->marcheCategory?->nom ?? 'Sans catégorie',
                    'repas_name' => $fiche->repas?->nom ?? 'Sans repas',
                    'plat_name' => $fiche->plat?->nom ?? 'Sans plat',
                    'article_id' => $ingredient->article?->id,
                    'article_code' => $ingredient->article?->reference,
                    'article_prix' => $ingredient?->prix_unitaire ?? 0,
                    'article_tva' => $ingredient->article?->taux_tva ?? 0,
                    'article_unite_mesure' => $ingredient->article?->unite_mesure,
                    'quantite' => $ingredient->quantite ?? 0,
                    'meal' => $fiche->meal
                ];
            });
        })->groupBy(['marche_category', 'article_id'])
            ->map(function ($articlesByCategory) {
                return $articlesByCategory->map(function ($articleGroup) {
                    // Build quantites structure dynamically
                    $meals = [Meal::PetitDejeuner, Meal::Dejeuner, Meal::Diner];
                    $plats = ['hors d\'oeuvres', 'plats', 'desserts', 'plats spéciaux'];

                    $quantites = [];

                    foreach ($meals as $meal) {
                        $mealKey = strtolower(str_replace('-', '_', $meal->value));
                        $quantites[$mealKey] = [];

                        foreach ($plats as $plat) {
                            $platKey = str_replace([' ', '\'', 'spéciaux'], ['_', '', 'special'], $plat);
                            $quantites[$mealKey][$platKey] = $articleGroup
                                ->where('meal', $meal)
                                ->where('repas_name', $plat)
                                ->value('quantite') ?? 0;
                        }
                    }

                    $totalTTC = $articleGroup->sum('quantite') * $articleGroup->first()['article_prix'] * (1 + ($articleGroup->first()['article_tva'] ?? 0) / 100);

                    return [
                        'quantites' => $quantites,
                        'article_id' => $articleGroup->first()['article_id'],
                        'article_code' => $articleGroup->first()['article_code'],
                        'article_prix' => $articleGroup->first()['article_prix'],
                        'article_unite_mesure' => $articleGroup->first()['article_unite_mesure'],
                        'total_ttc' => $totalTTC
                    ];
                });
            });

        $fileName = "menu-collectivite-{$menu->date->format('Y-m-d')}.pdf";
        return Pdf::view('pdf.menu-collectivite', compact('repas', 'menu', 'data'))
            ->headerView('pdf.H')
            ->footerView('pdf.F')
            ->margins(45, 5, 40, 5)
            ->orientation(Orientation::Landscape)
            ->format(Format::A4)
            ->download($fileName);
    }

    public function createExport()
    {

        return Inertia::modal('MenusCollectivites/CreateExportModal')->baseRoute('menus.index');
    }

    public function export(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);

        $menus = MenuCollectivite::whereDate('date', '>=', $request->start_date)
            ->with('fiches', 'fiches.plat', 'fiches.repas')
            ->whereDate('date', '<=', $request->end_date)
            ->orderBy('date')
            ->get();


        $fileName = "menu-collectivite-{$request->start_date}-{$request->end_date}.pdf";
        return Pdf::view('pdf.export-menu-collectivite', [
            'menus' => ExportMenuCollectiviteResource::collection($menus)->toArray($request),
            'startDate' => Carbon::parse($request->start_date),
            'endDate' => Carbon::parse($request->end_date),
        ])
            ->headerView('pdf.H')
            ->footerView('pdf.F')
            ->margins(45, 0, 40, 0)
            ->orientation(Orientation::Landscape)
            ->format(Format::A4)
            ->download($fileName);

    }
}
