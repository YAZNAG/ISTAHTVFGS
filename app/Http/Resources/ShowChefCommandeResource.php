<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShowChefCommandeResource extends JsonResource
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
            'statut' => $this->statut,
            'articles_count' => count($this->items),
            'created_at' => $this->created_at->toDateString(),
            'note' => $this->note,
            'validation_date' => $this->validation_date?->toDateString(),
            'validation_note' => $this->validation_note,
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
