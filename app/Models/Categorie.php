<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Categorie extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'nom',
        'couleur',
        'est_actif',
    ];

    protected $casts = [
        'est_actif' => 'boolean',
    ];

    public function articles()
    {
        return $this->hasMany(Article::class);
    }

    public function marches()
    {
        return $this->hasMany(BonCommande::class, 'categorie_id');
    }

    public function chefCommandes()
    {
        return $this->hasMany(ChefCommande::class, 'categorie_id');
    }

    public function scopeActives($query)
    {
        return $query->where('est_actif', true);
    }
}
