<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EditDemendeResource extends JsonResource
{
    public static $wrap = null;
    
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
            'demandeur_id' => $this->demandeur_id,
            'fiche_id' => $this->fiche_id,
            'articles' => $this->articles->map(function ($article) {
                return [
                    'id' => $article->id,
                    'article_id' => $article->article_id,
                    'quantite_demandee' => $article->quantite_demandee,
                    'designation' => $article->article->designation
                ];
            })
        ];
    }
}
