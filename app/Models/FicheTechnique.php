<?php

namespace App\Models;

use App\Enums\FicheType;
use App\Enums\Meal;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FicheTechnique extends Model
{
    use HasFactory;

    protected $table = 'fiches_techniques';
    
    protected $fillable = [
        'type',
        'repas_id',
        'plat_id',
        'responsable',
        'effectif',
        'menu_collectivite_id',
        'meal',
        'created_by',
    ];

    protected $casts = [
        'type' => FicheType::class,
        'meal' => Meal::class
    ];


    public function scopeCollectivite($query)
    {
        return $query->where('type', FicheType::COLLECTIVITE);
    }

    public function scopePedagogique($query)
    {
        return $query->where('type', FicheType::PEDAGOGIQUE);
    }

    public function repas()
    {
        return $this->belongsTo(Repas::class, 'repas_id');
    }

    public function plat()
    {
        return $this->belongsTo(Plat::class, 'plat_id');
    }
    
    public function etapes()
    {
        return $this->hasMany(Etape::class, 'fiche_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function demande()
    {
        return $this->hasOne(Demande::class, 'fiche_id');
    }

    public function ingredients()
    {
        // return $this->hasManyThrough(Ingredient::class, Etape::class, 'fiche_id', 'etape_id', 'id', 'id');
        return $this->hasMany(Ingredient::class, 'fiche_id');
    }

    public function menu()
    {
        return $this->belongsTo(MenuCollectivite::class, 'menu_collectivite_id');
    }
    
}
