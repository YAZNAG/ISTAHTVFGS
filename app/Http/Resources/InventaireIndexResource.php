<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventaireIndexResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $totalLines = $this->lignes->count();
        $filledLignes = $this->lignes->whereNotNull('stock_reel')->count();

        return [
            'id' => $this->id,
            'mois' => $this->mois,
            'statut' => $this->statut,
            'finalized_at' => $this->finalized_at,
            'progress' => $filledLignes . '/' . $totalLines,
            'articles_count' => $totalLines,
            'created_at' => $this->created_at->toDateString(),
        ];
    }
}
