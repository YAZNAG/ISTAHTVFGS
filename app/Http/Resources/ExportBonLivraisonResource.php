<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExportBonLivraisonResource extends JsonResource
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
            'reference' => $this->numero,
            'statut' => $this->statut,
            'date_livraison' => $this->date_livraison?->toDateString(),
            'created_at' => $this->created_at?->toDateString(),
            'receptionne_par' => $this->responsable->name,

            'fournisseur' => $this->whenLoaded('fournisseur', function () {
                return [
                    'id' => $this->fournisseur->id,
                    'nom' => $this->fournisseur->nom,
                    'contact' => $this->fournisseur->contact,
                    'email' => $this->fournisseur->email,
                    'adresse' => $this->fournisseur->adresse,
                    // 'logo' => $this->fournisseur->getFirstMedia('logo'),
                    'logo' => public_path('storage/'. $this->fournisseur->getFirstMedia('logo')->id . '/' . $this->fournisseur->getFirstMedia('logo')->file_name),
                    // 'm' => 'storage/'. $this->fournisseur->getMedia('logo')->id . '/' . $this->fournisseur->getMedia('logo')->file_name 
                ];
            }),

            'lignes' => $this->whenLoaded('items', function () {
                return $this->items->map(function ($ligne) {
                    return [
                        'id' => $ligne->id,
                        'designation' => $ligne->article->designation,
                        'unite_mesure' => $ligne->article->unite_mesure,
                        'quantite_livree' => (float) $ligne->quantite,
                        'prix_unitaire' => (float) $ligne->prix_unitaire,
                        'tva' => (float) ($ligne->taux_tva ?? 0),
                        'total_ht' => (float) ($ligne->montant_ht),
                        'total_ttc' => (float) ($ligne->montant_ttc),
                    ];
                });
            }),

            'totaux' => [
                'ht' => floatval($this->total_ht),
                'tva' => floatval($this->total_tva),
                'ttc' => floatval($this->total_ttc),
            ],
        ];
    }
}
