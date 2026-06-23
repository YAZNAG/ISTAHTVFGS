<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShowBonCommandeResource extends JsonResource
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
            'reference' => $this->reference,
            'date_mise_ligne' => $this->date_mise_ligne,
            'date_debut' => $this->date_debut,
            'date_fin' => $this->date_fin,
            'annule_at' => $this->annule_at,
            'reason_annulation' => $this->reason_annulation,
            'created_at' => $this->created_at,
            'date_limite_reception' => $this->date_limite_reception,
            'fournisseur' => [
                'nom' => $this->fournisseur?->nom,
                'contact' => $this->fournisseur?->contact,
            ],
            'categorie_principale' => $this->categorie?->nom,
            'categorie' => $this->categorie?->nom,
            'date_debut' => $this->date_debut,
            'date_fin' => $this->date_fin,
            'statut' => $this->statut,
            'totalHt' => $this->articles->sum('montant_ht'),
            'totalTTC' => $this->articles->sum('montant_ttc'),
            'totalTVA' => $this->articles->sum('montant_tva'),
            
            'lignes' => $this->articles->map(function ($ligne) {
                return [
                    'code' => $ligne->article?->reference,
                    'designation' => $ligne->article?->designation,
                    'unite_mesure' => $ligne->article?->unite_mesure,
                    'quantite_minimale' => $ligne->quantite_minimale ?? 0,
                    'quantite_maximale' => $ligne->quantite_maximale ?? $ligne->quantite_commandee,
                    'quantite' => $ligne->quantite_maximale ?? $ligne->quantite_commandee,
                    'prix_unitaire' => $ligne->prix_unitaire_ht,
                    'tva' => $ligne->taux_tva,
                    'total_ttc' => $ligne->montant_ttc
                ];
            }),

        ];
    }
}
