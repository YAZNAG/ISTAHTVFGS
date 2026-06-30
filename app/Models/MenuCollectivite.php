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
        'effectif_petit_dejeuner',
        'effectif_dejeuner',
        'effectif_diner',
    ];

    public function effectifPourRepas(string $meal): int
    {
        return match ($meal) {
            'petit_dejeuner', 'petit-dejeuner' => (int) ($this->effectif_petit_dejeuner ?? $this->effectif ?? 0),
            'diner' => (int) ($this->effectif_diner ?? $this->effectif ?? 0),
            default => (int) ($this->effectif_dejeuner ?? $this->effectif ?? 0),
        };
    }

    public function casts()
    {
        return [
            'date' => 'date',
        ];
    }

    public function fiches()
    {
        return $this->hasMany(FicheTechnique::class, 'menu_collectivite_id');
    }
}
