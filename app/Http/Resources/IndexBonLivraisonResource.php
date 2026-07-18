<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IndexBonLivraisonResource extends JsonResource
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
            'fournisseur' => $this->fournisseur->nom,
            'date_livraison' => $this->date_livraison?->toDateString(),
            'statut' => $this->statut,
            'items_count' => $this->items_count,
        ];
    }
}
