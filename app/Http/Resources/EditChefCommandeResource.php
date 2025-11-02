<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EditChefCommandeResource extends JsonResource
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
            'numero' => $this->numero,
            'created_at' => $this->created_at->toDateString(),
            'categorie_id' => $this->categorie_id,
            'items' => $this->items->map(function ($item) {
                return [
                    'quantite' => $item->quantite_commandee,
                    'article' => [
                        'id' => $item->article->id,
                        'designation' => $item->article->designation,
                        'unite_mesure' => $item->article->unite_mesure,
                    ],
                ];
            }),
        ];
    }
}
