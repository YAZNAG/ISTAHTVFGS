<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MesDemendesResource extends JsonResource
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
            'motif' => $this->motif,
            'statut' => $this->statut,
            'date_demande' => $this->date_demande,
            'date_validation' => $this->date_validation,
            'valide_par' => $this->valideur?->name,
            'fiche_technique' => $this->getFirstMediaUrl('fiches_techniques'),
            'articles_count' => $this->articles_count ?? $this->articles()->count()
        ];
    }
}
