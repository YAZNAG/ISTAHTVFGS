<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShowReceptionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        
        $bonLivraison = $this->bonLivraison;
        return [
            'id' => $this->id,
            'numero' => $this->numero,
            'date_livraison' => $bonLivraison->date_livraison?->toDateString(),
            'receptionne_par' => $bonLivraison->responsable->name,
            'created_at' => $this->created_at?->toDateString(),
            'total_ttc' => (float) $bonLivraison->total_ttc,

            // Fournisseur (relation)
            'fournisseur' => [
                'id' => $bonLivraison->fournisseur->id,
                'nom' => $bonLivraison->fournisseur->nom,
                'contact' => $bonLivraison->fournisseur->contact,
                'email' => $bonLivraison->fournisseur->email,
                'adresse' => $bonLivraison->fournisseur->adresse,
            ],

            // Lignes de livraison (relation)
            'items' => $bonLivraison->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    // 'code' => $item->code,
                    'designation' => $item->article->designation,
                    'unite_mesure' => $item->article->unite_mesure,
                    'quantite' => (float) $item->quantite,
                    'prix_unitaire' => (float) $item->prix_unitaire,
                    'taux_tva' => (float) $item->taux_tva,
                    'total_ttc' => (float) $item->montant_ttc,
                ];
            }),
            
        ];
    }
}
