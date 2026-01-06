<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuCollectivite extends Model
{
    protected $table = 'menu_collectivites';

    protected $fillable = [
        'date',
        'responsable',
        'effectif',
    ];

    public function fiches()
    {
        return $this->hasMany(FicheTechnique::class, 'menu_collectivite_id');
    }
}
