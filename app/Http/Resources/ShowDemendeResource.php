<?php

namespace App\Http\Resources;

use App\Enums\DemandeStatut;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShowDemendeResource extends JsonResource
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
            'demandeur_id' => $this->demandeur_id,
            'validateur' => $this->valideur?->name,
            'commentaire_validation' => $this->commentaire_validation,
            'date_validation' => $this->date_validation,
            'motif' => $this->motif,
            'statut' => $this->statut,
            'statut_label' => DemandeStatut::from($this->statut)->label(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'articles' => $this->articles->map(function ($article) {
                return [
                    'id' => $article->id,
                    'article_id' => $article->article_id,
                    'designation' => $article->article->designation,
                    'quantite_demandee' => $article->quantite_demandee,
                    'quantite_stock' => $article->article->quantite_stock,
                    'unite_mesure' => $article->article->unite_mesure
                ];
            }),
        ];
    }
}
