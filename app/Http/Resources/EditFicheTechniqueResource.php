<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EditFicheTechniqueResource extends JsonResource
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
            'type' => $this->type,
            'type_label' => $this->type_label,
            'repas_id' => $this->repas_id,
            'plat_id' => $this->plat_id,
            'responsable' => $this->responsable,
            'effectif' => $this->effectif,
            'demandeur_id' => $this->created_by,
            'etapes' => $this->etapes->map(function ($etape) {
                return [
                    'id' => $etape->id,
                    'title' => $etape->title,
                    'description' => $etape->description
                ];
            }),
            'articles' => $this->ingredients->map(function ($article) {
                return [
                    'article_id' => $article->article_id,
                    'designation' => $article->article->designation,
                    'quantite' => $article->quantite,
                ];
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
