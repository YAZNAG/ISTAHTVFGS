<?php

namespace App\Http\Resources;

use App\Enums\Meal;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExportMenuCollectiviteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'date'           => $this->date,
            'petit_dejeuner' => $this->mealGroup(Meal::PetitDejeuner),
            'dejeuner'       => $this->mealGroup(Meal::Dejeuner),
            'diner'          => $this->mealGroup(Meal::Diner),
        ];
    }

    private function mealGroup($mealEnum): array
    {
        return [
            'hors_doeuvres' => $this->dishName($mealEnum, 'hors d\'oeuvres'),
            'plat'          => $this->dishName($mealEnum, 'plats'),
            'dessert'       => $this->dishName($mealEnum, 'desserts'),
            'plat_special'  => $this->dishName($mealEnum, 'plats spéciaux'),
        ];
    }

    private function dishName($meal, $repasNom): ?string
    {
        return $this->fiches
            ->where('meal', $meal)
            ->where('repas.nom', $repasNom)
            ->first()
            ->plat
            ->nom ?? null;
    }
}
