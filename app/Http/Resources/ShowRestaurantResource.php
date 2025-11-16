<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShowRestaurantResource extends JsonResource
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
            // 'created_by' => $this->user?->name,
            'ingredients' => $this->items->map(function ($item) {
                    return [
                        'code' => $item->article->reference,
                        'designation' => $item->article->designation,
                        'unite_mesure' => $item->article->unite_mesure,
                        'prix_unitaire' => $item->prix_unitaire,
                        'taux_tva' => $item->taux_tva,
                        'quantite' => $item->quantite,
                        'total_ttc' => $item->total_ttc,
                    ];
                }),
            'created_at' => $this->created_at?->format('Y-m-d H:i'),
        ];
    }
}
