<?php

namespace App\Models;

use App\Enums\FicheType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FicheTechnique extends Model
{
    use HasFactory;

    protected $table = 'fiches_techniques';
    
    protected $fillable = [
        'type',
        'nom',
        'plat',
        'responsable',
        'effectif',
        'created_by',
    ];

    protected $casts = [
        'type' => FicheType::class,
    ];


    public function scopeCollectivite($query)
    {
        return $query->where('type', FicheType::COLLECTIVITE);
    }

    public function scopePedagogique($query)
    {
        return $query->where('type', FicheType::PEDAGOGIQUE);
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
        return $this->hasManyThrough(Ingredient::class, Etape::class, 'fiche_id', 'etape_id', 'id', 'id');
    }
    
}
