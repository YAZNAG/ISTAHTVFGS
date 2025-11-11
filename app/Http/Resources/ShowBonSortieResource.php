<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShowBonSortieResource extends JsonResource
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
            'type' => $this->type_sortie,
            'date' => $this->date_sortie?->toDateString(),
            'articles' => $this->lignesSortie->map(function ($ligne) {
                return [
                    'id' => $ligne->id,
                    'article_id' => $ligne->article_id,
                    'designation' => $ligne->article->designation,
                    'unite_mesure' => $ligne->article->unite_mesure,
                    'quantite' => $ligne->quantite,
                    'taux_tva' => "" . $ligne->taux_tva,
                    'prix_unitaire' => $ligne->prix_unitaire,
                    'quantite_stock' => $ligne->article->quantite_stock
                ];
            }),
            'demandeur' => $this->demandeur?->name ?? '---',
            'created_by' => $this->createdBy?->name ?? 'Système',
        ];
    }
}
