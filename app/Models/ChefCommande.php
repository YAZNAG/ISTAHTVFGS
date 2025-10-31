<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChefCommande extends Model
{
    ## Status
    public const STATUS_CREE = 'cree';
    public const STATUS_EN_ATTENTE_VALIDATION = 'en_attente_validation';
    public const STATUS_EN_ATTENTE_LIVRAISON = 'en_attente_livraison';
    public const STATUS_LIVRE_COMPLETEMNT = 'livre_completement';
    public const STATUS_LIVRE_PARTIELLEMENT = 'livre_partiellement';
    public const STATUS_ANNULEE = 'annulee';


    public $fillable = [
        'numero',
        'note',
        'statut',
        'user_id',
        'annulee_at',
        'validation_note',
    ];

    public static function genererNumero() // Retourne un int au lieu de string
    {
        $lastNumber = (int) self::max('id') ?? 0;
        $numero = 'BC-' . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        return $numero;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(ChefCommandeItem::class, 'chef_commande_id', 'id');
    }

    public function articles()
    {
        return $this->hasManyThrough(Article::class, ChefCommandeItem::class, 'chef_commande_id', 'id', 'id', 'article_id');
    }

}
