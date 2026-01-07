<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Repas extends Model
{
    protected $table = 'repas';

    protected $fillable = [
        'nom',
    ];

    public function plats()
    {
        return $this->hasMany(Plat::class, 'repas_id');
    }
}
