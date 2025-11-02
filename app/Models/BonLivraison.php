<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BonLivraison extends Model
{
    public const STATUS_EN_ATTENTE_LIVRAISON = 'en_attente_livraison';
    public const STATUS_LIVREE = 'livree';
    
    public $fillable = [
        'numero',
        'statut',
        'date_livraison',
        'bon_commande_id',
        'notes',
        'created_by',
        'fournisseur_id',
    ];

    public function casts()
    {
        return [
            'date_livraison' => 'datetime',
        ];
    }

    public static function genererNumero()
    {
        $lastNumber = (int) self::max('id') ?? 0;
        $numero = 'BL-' . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        return $numero;
    }

    public function chefCommande()
    {
        return $this->belongsTo(ChefCommande::class, 'chef_commande_id', 'id');
    }

    public function items()
    {
        return $this->hasMany(BonLivraisonItem::class, 'bon_livraison_id', 'id');
    }

    public function articles()
    {
        return $this->hasManyThrough(Article::class, BonLivraisonItem::class, 'bon_livraison_id', 'id', 'id', 'article_id');
    }

    public function fournisseur()
    {
        return $this->belongsTo(Fournisseur::class);
    }


    public function scopePending($query)
    {
        return $query->where('statut', self::STATUS_EN_ATTENTE_LIVRAISON);
    }

}
