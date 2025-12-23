<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Decompte extends Model
{
    protected $table = 'decomptes';

    protected $fillable = [
        'date',
        'marche_id',
        'final',
    ];

    public function casts()
    {
        return [
            'date' => 'date',
        ];
    }

    public function marche()
    {
        return $this->belongsTo(BonCommande::class, 'marche_id');
    }
    
}
