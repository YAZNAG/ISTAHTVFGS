<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IndexSortieStockResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'date_sortie' => $this->date_mouvement->format('Y-m-d'),
            'code_article' => $this->article->reference,
            'designation_article' => $this->article->designation,
            'unite_mesure' => $this->article->unite_mesure,
            'stock_initial' => $this->quantite_actuelle + $this->quantite_sortie,
            'quantite_sortie' => $this->quantite_sortie,
            'reference_bon_sortie' => $this->referenceable?->numero ?? '---',
            'stock_actuel' => $this->quantite_actuelle,
        ];
    }
}
