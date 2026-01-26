<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShowFournisseurResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [

            "id" => $this->id,
            "nom" => $this->nom,
            "contact" => $this->contact,
            "telephone" => $this->telephone,
            "email" => $this->email,
            "adresse" => $this->adresse,
            "ville" => $this->ville,
            "ice" => $this->ice,
            'tp' => $this->tp,
            'rc' => $this->rc,
            'if' => $this->if,
            'cb' => $this->cb,
            'cnss' => $this->cnss,
            "est_actif" => $this->est_actif,
            "notes" => $this->notes,
            "logo" => $this->getFirstMediaUrl('logo'),
            "raison_sociale" => $this->raison_sociale,
            "created_at" => $this->created_at->toDateString(),
            "bon_commandes_count" => $this->bon_commandes_count,

            "bon_commandes" => $this->bonCommandes->map(function ($bon_commande) {
                return [
                    "id" => $bon_commande->id,
                    "reference" => $bon_commande->reference,
                    "date_mise_ligne" => $bon_commande->date_mise_ligne->toDateString(),
                    "date_limite_reception" => $bon_commande->date_limite_reception->toDateString(),
                    "statut" => $bon_commande->statut,
                    "articles_count" => $bon_commande->articles()->count() 
                ];
            })
        ];
    }
}
