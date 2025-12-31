<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ingredient extends Model
{
    use HasFactory;

    protected $table = 'ingredients';

    protected $append = ['total_ttc'];
    
    protected $fillable = [
        'prix_unitaire',
        'quantite',
        'taux_tva',
        'article_id',
        'fiche_id',
    ];

    public function casts()
    {
        return [
            'quantite' => 'decimal:2',
            'prix_unitaire' => 'decimal:2',
            'taux_tva' => 'decimal:2',
        ];
    }

    public function getTotalTtcAttribute() {
        $ttc = $this->prix_unitaire * $this->quantite * (1 + $this->taux_tva / 100);
        return round($ttc, 2);
    }


    public function article()
    {
        return $this->belongsTo(Article::class, 'article_id');
    }

    public function etape()
    {
        return $this->belongsTo(Etape::class, 'etape_id');
    }
}
