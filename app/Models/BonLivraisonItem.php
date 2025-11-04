<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BonLivraisonItem extends Model
{
    protected $table = 'bon_livraisons_items';
    
    public $fillable = [
        'quantite',
        'prix_unitaire',
        'taux_tva',
        // 'montant_tva',
        // 'prix_total',
        'bon_livraison_id',
        'article_id',
    ];

    protected $casts = [
        'quantite' => 'float',
        'prix_unitaire' => 'float',
        'taux_tva' => 'float',
    ];

    protected $appends = ['montant_ht', 'montant_tva', 'montant_ttc'];

    public function getMontantTtcAttribute() {
        $ttc = $this->prix_unitaire * $this->quantite * (1 + $this->taux_tva / 100);
        return round($ttc, 2);
    }

    public function getMontantHtAttribute() {
        $ht = $this->prix_unitaire * $this->quantite ;
        return round($ht, 2);
    }

    public function getMontantTvaAttribute() {
        $tva = $this->montant_ht * ($this->taux_tva / 100);
        return round($tva, 2);
    }
    
    public function bonLivaison()
    {
        return $this->belongsTo(BonLivraison::class, 'bon_livraison_id', 'id');
    }

    public function article() 
    {
        return $this->belongsTo(Article::class);
    }
}
