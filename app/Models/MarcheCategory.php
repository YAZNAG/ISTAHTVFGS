<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class MarcheCategory extends Model
{
    protected $table = 'marche_categories';

    protected $fillable = [
        'nom',
        'est_actif',
    ];


    /**
     * Scope a query to only include active users.
     */
    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('est_actif', true);
    }
}
