<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Etape extends Model
{
    use HasFactory;

    protected $table = 'etapes';
    
    protected $fillable = [
        'title',
        'description',
        'fiche_id',
    ];


    public function fiche()
    {
        return $this->belongsTo(FicheTechnique::class, 'fiche_id');
    }

}
