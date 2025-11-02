<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BonLivraisonItem extends Model
{
    public $fillable = [
        'quantite',
        'prix_unitaire',
        'taux_tva',
        'montant_tva',
        'prix_total',
        'bon_livraison_id',
        'article_id',
    ];

    public function bonLivaison()
    {
        return $this->belongsTo(BonLivraison::class, 'bon_livraison_id', 'id');
    }

    public function articles() 
    {
        return $this->belongsTo(Article::class);
    }
}
