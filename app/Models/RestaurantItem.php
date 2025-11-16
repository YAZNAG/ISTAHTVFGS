<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RestaurantItem extends Model
{
    protected $fillable = [
        'restaurant_id', 
        'article_id',
        'quantite',
        'prix_unitaire',
        'taux_tva',
    ];

    public function getTotalTtcAttribute() {
        $ttc = $this->prix_unitaire * $this->quantite * (1 + $this->taux_tva / 100);
        return round($ttc, 2);
    }

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }
    
    public function article()
    {
        return $this->belongsTo(Article::class);
    }
    
}
