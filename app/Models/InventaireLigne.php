<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventaireLigne extends Model
{
    protected $table = 'inventaire_lignes';

    protected $fillable = [
        'inventaire_id',
        'code_article',
        'designation',
        'qte_entree',
        'qte_sortie',
        'stock_theorique',
        'stock_reel',
        'ecart',
        'observations',
    ];

    /* -----------------------------------------------------------------
     |  Accessors
     * -----------------------------------------------------------------*/
    protected $appends = ['ecart'];

    public function getEcartAttribute(): int
    {
        return $this->stock_reel - $this->stock_theorique;
    }

    /* -----------------------------------------------------------------
     |  Relations
     * -----------------------------------------------------------------*/
    public function inventaire(): BelongsTo
    {
        return $this->belongsTo(Inventaire::class);
    }
}
