<?php
// app/Models/SortieStock.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SortieStock extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'numero',
        'type_sortie',
        'demandeur_id',
        'date_sortie',
        'motif',
        'notes',
        'statut',
        'demande_id',
        'created_by',
        'date_validation',
        'valide_par',
    ];

    protected $casts = [
        'date_sortie' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Types de sortie
    const TYPE_VENTE = 'vente';
    const TYPE_TRANSFERT = 'transfert';
    const TYPE_PERTE = 'perte';
    const TYPE_AJUSTEMENT = 'ajustement';

    // Statuts
    const STATUT_ATTENTE_VALIDATION = 'attente_validation';
    const STATUT_ATTENTE_LIVRAISON = 'attente_livraison';
    const STATUT_LIVREE = 'livree';
    const STATUT_VALIDE = 'valide';
    const STATUT_ANNULE = 'annule';

    const TYPE_DEMANDE = 'demande';

    public function valideur()
    {
        return $this->belongsTo(User::class, 'valide_par');
    }

    public function demande(): BelongsTo
    {
        return $this->belongsTo(Demande::class, 'demande_id');
    }

    public function demandeur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'demandeur_id');
    }

    public function lignesSortie(): HasMany
    {
        return $this->hasMany(LigneSortieStock::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Accessor pour le numéro d'affichage
    public function getNumeroAffichageAttribute(): string
    {
        return 'SORT-' . str_pad($this->id, 6, '0', STR_PAD_LEFT);
    }

    // Méthode pour calculer le total
    public function getTotalAttribute(): float
    {
        return $this->lignesSortie->sum('prix_total');
    }

    // Méthode pour générer le numéro automatique
    public static function genererNumero() // Retourne un int au lieu de string
    {
        $lastNumber = (int) self::withTrashed()->max('id') ?? 0;
        return 'SORT-' . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
    }


    // Annuler les mouvements associés
    public function annulerMouvements(): void
    {
        MouvementStock::where('source_type', self::class)
            ->where('source_id', $this->id)
            ->delete();
    }

    // Vérifier la disponibilité du stock avant création
    public function verifierDisponibiliteStock(): bool
    {
        foreach ($this->lignesSortie as $ligne) {
            if ($ligne->article->quantite_stock < $ligne->quantite) {
                return false;
            }
        }
        return true;
    }
}