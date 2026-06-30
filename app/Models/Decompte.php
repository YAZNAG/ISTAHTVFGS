<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Decompte extends Model
{
    protected $table = 'decomptes';

    protected $fillable = [
        'date',
        'date_debut',
        'marche_id',
        'categorie_id',
        'final',
    ];

    public function casts()
    {
        return [
            'date' => 'date',
            'date_debut' => 'date',
        ];
    }

    public function marche()
    {
        return $this->belongsTo(BonCommande::class, 'marche_id');
    }

    public function categorie()
    {
        return $this->belongsTo(Categorie::class, 'categorie_id');
    }

    public function items()
    {
        return $this->hasMany(DecompteItem::class);
    }
}
