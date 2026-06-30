<?php

namespace App\Http\Resources;

use App\Enums\Meal;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

class EditMenuCollectiviteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        $petit = $this->mapByRepas($this->fiches, Meal::PetitDejeuner);
        $dej   = $this->mapByRepas($this->fiches, Meal::Dejeuner);
        $din   = $this->mapByRepas($this->fiches, Meal::Diner);
        
        return [
            'id' => $this->id,
            'date' => $this->date,
            'responsable' => $this->responsable,
            'effectif' => $this->effectif,
            'effectif_petit_dejeuner' => $this->effectif_petit_dejeuner ?? $this->effectif,
            'effectif_dejeuner' => $this->effectif_dejeuner ?? $this->effectif,
            'effectif_diner' => $this->effectif_diner ?? $this->effectif,
            'menus' => [
                'petit_dejeuner' => $this->buildMenuPart($petit),
                'dejeuner'       => $this->buildMenuPart($dej),
                'diner'          => $this->buildMenuPart($din),
            ]
        ];
    }

    private function mapByRepas(Collection $fiches, Meal $meal): Collection
    {
        return $fiches
            ->where('meal', $meal)
            ->keyBy(fn ($f) => strtolower($f->repas->nom));
    }

    private function buildMenuPart(Collection $map): array
    {
        return [
            'entree'       => $map->get('hors d\'oeuvres')?->id,
            'plat'         => $map->get('plats')?->id,
            'dessert'      => $map->get('desserts')?->id,
            'plat_special' => $map->get('plats spéciaux')?->id ?? "",
        ];
    }
}
