<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShowFicheTechniqueResource extends JsonResource
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
            'type_label' => $this->type,
            'type' => $this->type->label(),
            'repas' => $this->repas->nom,
            'plat' => $this->plat->nom,
            'responsable' => $this->responsable,
            'effectif' => $this->effectif,
            'created_by' => $this->user?->name,
            'etapes' => $this->etapes->map(function ($etape) {
                return [
                    'id' => $etape->id,
                    'title' => $etape->title,
                    'description' => $etape->description
                ];
            }),
            'ingredients' => $this->ingredients->map(function ($ingredient) {
                return [
                    'code' => $ingredient->article->reference,
                    'designation' => $ingredient->article->designation,
                    'unite_mesure' => $ingredient->article->unite_mesure,
                    'prix_unitaire' => $ingredient->prix_unitaire,
                    'taux_tva' => $ingredient->taux_tva,
                    'quantite' => $ingredient->quantite,
                    'total_ttc' => $ingredient->total_ttc,
                ];
            }),
            'created_at' => $this->created_at?->format('Y-m-d H:i'),
        ];

    }
}
