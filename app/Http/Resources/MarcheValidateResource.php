<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MarcheValidateResource extends JsonResource
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
            'articles' => $this->articles->map(function ($article)  {
                return [
                    'id' => $article->id,
                    'designation' => $article->article->designation,
                    'reference' => $article->article->reference,
                    'unite_mesure' => $article->article->unite_mesure,
                    'quantite_commandee' => $article->quantite_commandee,
                    'taux_tva' => $article->taux_tva
                ];
            })
        ];
    }
}
