<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BonLivraison extends Model
{
    public const STATUS_EN_ATTENTE_LIVRAISON = 'en_attente_livraison';
    public const STATUS_PARTIELLEMENT_LIVREE = 'partiellement_livree';
    public const STATUS_LIVREE = 'livree';
    
    public $fillable = [
        'numero',
        'statut',
        'date_livraison',
        'chef_commande_id',
        'notes',
        'created_by',
        'fournisseur_id',
        'responsable_id',
    ];

    protected $appends = ['total_ht', 'total_tva', 'total_ttc'];

    public function casts()
    {
        return [
            'date_livraison' => 'datetime',
            'total_ht' => 'decimal:2',
            'total_tva' => 'decimal:2',
            'total_ttc' => 'decimal:2',
        ];
    }

    public static function genererNumero()
    {
        $lastNumber = (int) self::max('id') ?? 0;
        $numero = 'BL-' . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        return $numero;
    }


    public function getTotalHtAttribute()
    {
        return number_format($this->items->sum('montant_ht'), 2, '.', '');
    }

    public function getTotalTvaAttribute()
    {
        return number_format($this->items->sum('montant_tva'), 2, '.', '');
    }

    public function getTotalTtcAttribute()
    {
        return number_format($this->items->sum('montant_ttc'), 2, '.', '');
    }

    public function reception()
    {
        return $this->hasOne(Reception::class, 'bon_livraison_id', 'id');
    }

    public function chefCommande()
    {
        return $this->belongsTo(ChefCommande::class, 'chef_commande_id', 'id');
    }

    public function responsable() 
    {
        return $this->belongsTo(User::class, 'responsable_id', 'id');    
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

    public function scopeLivree($query)
    {
        return $query->where('statut', self::STATUS_LIVREE);
    }

}
