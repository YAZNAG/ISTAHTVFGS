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

    /**
     * Articles agreges de toutes les fiches du menu (somme des quantites par article).
     * Sert de base a la creation d'une demande de collectivite.
     */
    public function articlesAgreges()
    {
        return $this->fiches
            ->flatMap(fn ($fiche) => $fiche->ingredients)
            ->groupBy('article_id')
            ->map(function ($lignes) {
                $first = $lignes->first();
                return [
                    'article_id'    => $first->article_id,
                    'designation'   => $first->article?->designation,
                    'unite_mesure'  => $first->article?->unite_mesure,
                    'quantite'      => (float) $lignes->sum('quantite'),
                    'prix_unitaire' => (float) ($first->prix_unitaire ?? 0),
                ];
            })
            ->values();
    }
}
