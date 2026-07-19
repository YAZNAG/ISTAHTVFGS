<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Inventaire extends Model
{
    protected $fillable = [
        'mois',
        'semaine',
        'categorie_id',
        'statut',
        'finalized_at',
    ];

    protected $casts = [
        'finalized_at' => 'datetime',
    ];

    public function lignes(): HasMany
    {
        return $this->hasMany(InventaireLigne::class);
    }

    public function categorie()
    {
        return $this->belongsTo(Categorie::class, 'categorie_id');
    }
}
