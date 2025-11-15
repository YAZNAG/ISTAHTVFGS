<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FicheTechniqueDemandeResource extends JsonResource
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
            'type' => $this->type,
            'articles' => $this->ingredients->map(function ($ingredient) {
                return [
                    'id' => $ingredient->article_id,
                    'designation' => $ingredient->article->designation,
                    'quantite' => $ingredient->quantite,
                    'prix_unitaire' => $ingredient->prix_unitaire,
                    'unite_mesure' => $ingredient->article->unite_mesure
                ];
            })
        ];
    }
}
