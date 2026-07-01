<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IndexReceptionResource extends JsonResource
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
            'bon_livraison_id' => $this->bonLivraison->id,
            'fournisseur' => $this->bonLivraison->fournisseur->nom,
            'date_livraison' => $this->bonLivraison->date_livraison?->toDateString(),
            'statut' => $this->bonLivraison->statut,
            'total_ht' => $this->bonLivraison->total_ht,
            'total_tva' => $this->bonLivraison->total_tva,
            'total_ttc' => $this->bonLivraison->total_ttc,
            'items_count' => $this->bonLivraison->items_count ?? 0,
        ];
    }
}
