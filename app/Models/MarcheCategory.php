<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarcheCategory extends Model
{
    protected $table = 'marche_categories';

    protected $fillable = [
        'nom',
        'est_actif',
    ];
}
