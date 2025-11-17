<?php

namespace App\Http\Controllers;

use App\Enums\FicheType;
use App\Http\Resources\FicheTechniqueDemandeResource;
use App\Http\Resources\RestaurantDemandeResource;
use App\Models\FicheTechnique;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ApiFicheController extends Controller
{
    public function getFicheByType(Request $request) {

        
        $user = auth()->user();
        $isAdmin = $user->isAdmin();
        
        // return response()->json(FicheType::values());

        if ($request->type == FicheType::RESTAURANT->value) {
            $restaurants = Restaurant::when(!$isAdmin, function ($query) use ($user) {
                return $query->where('created_by', $user->id);
            })->with(['items', 'items.article'])->get();

            return RestaurantDemandeResource::collection($restaurants);
        }

        $fiche = FicheTechnique::when(!$isAdmin, function ($query) use ($user) {
            return $query->where('created_by', $user->id);
        })->with(['ingredients', 'ingredients.article'])->where('type', $request->type)
        ->get();



        return FicheTechniqueDemandeResource::collection($fiche);
    }
}
