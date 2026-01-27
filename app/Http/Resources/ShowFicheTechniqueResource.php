<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShowFicheTechniqueResource extends JsonResource
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
            'type_label' => $this->type,
            'type' => $this->type->label(),
            'repas' => $this->repas->nom,
            'plat' => $this->plat->nom,
            'responsable' => $this->responsable,
            'effectif' => $this->effectif,
            'created_by' => $this->user?->name,
            'etapes' => $this->etapes->map(function ($etape) {
                return [
                    'id' => $etape->id,
                    'title' => $etape->title,
                    'description' => $etape->description
                ];
            }),
            'ingredients' => $this->ingredients->map(function ($ingredient) {
                return [
                    'code' => $ingredient->article->reference,
                    'designation' => $ingredient->article->designation,
                    'unite_mesure' => $ingredient->article->unite_mesure,
                    'prix_unitaire' => $ingredient->prix_unitaire,
                    'taux_tva' => $ingredient->taux_tva,
                    'quantite' => $ingredient->quantite,
                    'total_ttc' => $ingredient->total_ttc,
                ];
            }),
            'created_at' => $this->created_at?->format('Y-m-d H:i'),
        ];

        /*
         <?php
[
    [
        'id' => 3,
        'prix_unitaire' => '15.00',
        'quantite' => '3.00',
        'taux_tva' => '7.00',
        'etape_id' => 5,
        'article_id' => 8,
        'created_at' => '2025-10-17T16:52:55.000000Z',
        'updated_at' => '2025-10-17T16:52:55.000000Z',
        'article' => [
            'id' => 8,
            'reference' => 'cardon-8',
            'designation' => 'Cardon',
            'description' => '',
            'quantite_stock' => '200.00',
            'categorie_id' => 1,
            'categorie_principale_id' => 1,
            'nature_prestation_id' => 1,
            'unite_mesure' => 'kg',
            'taux_tva' => '0.00',
            'seuil_minimal' => 0,
            'seuil_maximal' => 0,
            'est_actif' => true,
            'created_at' => '2025-09-29T10:18:03.000000Z',
            'updated_at' => '2025-10-17T14:48:04.000000Z'
        ]
    ]
];
?>
         */
    }
}
