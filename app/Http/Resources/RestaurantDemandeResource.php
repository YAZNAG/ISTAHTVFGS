<?php

namespace App\Http\Resources;

use App\Enums\FicheType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RestaurantDemandeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'type' => FicheType::RESTAURANT->value,
            'articles' => $this->items->map(function ($item) {
                return [
                    'id' => $item->article_id,
                    'designation' => $item->article->designation,
                    'quantite' => $item->quantite,
                    'prix_unitaire' => $item->prix_unitaire,
                    'unite_mesure' => $item->article->unite_mesure
                ];
            })
        ];
    }
}
