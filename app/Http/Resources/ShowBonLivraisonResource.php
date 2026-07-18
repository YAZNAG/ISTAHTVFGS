<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShowBonLivraisonResource extends JsonResource
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
            'date_livraison' => $this->date_livraison?->toDateString(),
            'receptionne_par' => $this->responsable->name,
            'created_at' => $this->created_at?->toDateString(),
            'annule_at' => $this->annule_at?->toDateString(),
            'reason_annulation' => $this->reason_annulation,

            // Fournisseur (relation)
            'fournisseur' => [
                'id' => $this->fournisseur->id,
                'nom' => $this->fournisseur->nom,
                'contact' => $this->fournisseur->contact,
                'email' => $this->fournisseur->email,
                'adresse' => $this->fournisseur->adresse,
            ],

            // Lignes de livraison (relation)
            'items' => $this->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    // 'code' => $item->code,
                    'designation' => $item->article->designation,
                    'unite_mesure' => $item->article->unite_mesure,
                    'quantite' => (float) $item->quantite,
                ];
            }),
        ];
    }
}
