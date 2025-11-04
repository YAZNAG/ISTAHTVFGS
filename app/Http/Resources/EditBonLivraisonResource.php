<?php

namespace App\Http\Resources;

use App\Models\BonCommande;
use App\Models\BonCommandeArticle;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EditBonLivraisonResource extends JsonResource
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
            'fournisseur' => $this->fournisseur->nom,
            'items_count' => $this->items_count,
            'items' => $this->items->map(function ($item) {
                $marcheArticle = BonCommandeArticle::where('bon_commande_id', $this->chefCommande->bon_commande_id)->where('article_id', $item->article_id)->first();

                return [
                    'id' => $item->id,
                    'article_id' => $item->article_id,
                    'designation' => $item->article->designation,
                    'quantite' => $item->quantite,
                    'prix_unitaire' => $marcheArticle->prix_unitaire_ht,
                    'taux_tva' => $item->taux_tva,
                ];
            })
        ];
    }
}
