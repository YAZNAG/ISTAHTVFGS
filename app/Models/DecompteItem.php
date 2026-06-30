<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DecompteItem extends Model
{
    protected $table = 'decompte_items';

    protected $fillable = [
        'decompte_id',
        'article_id',
        'quantite',
        'prix_unitaire',
        'taux_tva',
        'montant_ht',
        'montant_tva',
        'montant_ttc',
    ];

    public function casts()
    {
        return [
            'quantite' => 'decimal:2',
            'prix_unitaire' => 'decimal:2',
            'taux_tva' => 'decimal:2',
            'montant_ht' => 'decimal:2',
            'montant_tva' => 'decimal:2',
            'montant_ttc' => 'decimal:2',
        ];
    }

    public function decompte()
    {
        return $this->belongsTo(Decompte::class, 'decompte_id');
    }

    public function article()
    {
        return $this->belongsTo(Article::class, 'article_id');
    }
}
