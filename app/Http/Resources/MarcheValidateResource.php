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
            'reference' => $this->reference,
            'objet' => $this->objet,
            'statut' => $this->statut,
            'fournisseur_id' => $this->fournisseur_id,
            'categorie' => $this->categorie?->nom,
            'articles' => $this->articles->map(function ($article)  {
                return [
                    'id' => $article->id,
                    'designation' => $article->article->designation,
                    'reference' => $article->article->reference,
                    'unite_mesure' => $article->article->unite_mesure,
                    'quantite_minimale' => $article->quantite_minimale ?? 0,
                    'quantite_maximale' => $article->quantite_maximale ?? $article->quantite_commandee,
                    'quantite_commandee' => $article->quantite_commandee,
                    'taux_tva' => $article->taux_tva,
                    'prix_unitaire_ht' => $article->prix_unitaire_ht,
                    'montant_ht' => $article->montant_ht,
                    'montant_tva' => $article->montant_tva,
                    'montant_ttc' => $article->montant_ttc,
                ];
            })
        ];
    }
}
