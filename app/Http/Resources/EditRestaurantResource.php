<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EditRestaurantResource extends JsonResource
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
            // 'responsable' => $this->responsable,
            'effectif' => $this->effectif,
            'articles' => $this->items->map(function ($item) {
                return [
                    'article_id' => $item->article_id,
                    'designation' => $item->article->designation,
                    'unite_mesure' => $item->article->unite_mesure,
                    'quantite' => $item->quantite,
                ];
            }),
        ];
    }
}
