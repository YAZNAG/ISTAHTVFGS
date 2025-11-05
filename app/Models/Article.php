<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'reference', 
        'designation', 
        'description', 
        'categorie_id', 
        'categorie_principale_id', 
        'nature_prestation_id', 
        'unite_mesure',
        'seuil_minimal', 
        'seuil_maximal', 
        'est_actif'
    ];

    protected $casts = [
        'est_actif' => 'boolean',
        'taux_tva' => 'decimal:2',
        'seuil_minimal' => 'integer',
        'seuil_maximal' => 'integer'
    ];

    public function categorie(): BelongsTo
    {
        return $this->belongsTo(Categorie::class);
    }

    public function categoriePrincipale(): BelongsTo
    {
        return $this->belongsTo(CategoriePrincipale::class);
    }

    public function naturePrestation(): BelongsTo
    {
        return $this->belongsTo(NaturePrestation::class, 'nature_prestation_id');
    }

    public function lignesEntreeStock()
    {
        return $this->hasMany(LigneEntreeStock::class, 'article_id', 'id');
    }

    public function mouvementsStock()
    {
        return $this->hasMany(MouvementStock::class, 'article_id', 'id');
    }
    
    public function lastEntryStock()
    {
        return $this->hasOne(MouvementStock::class)
            ->entrees()
            ->latestOfMany('date_mouvement');
    }
    
    public function images(): HasMany
    {
        return $this->hasMany(ArticleImage::class);
    }

    public function scopeActives($query)
    {
        return $query->where('est_actif', true);
    }

    public function scopeLowStock($query)
    {
        return $query->where('stock_actuel', '<=', \DB::raw('seuil_minimal'));
    }

    public function getEstEnRuptureAttribute()
    {
        return $this->stock_actuel <= $this->seuil_minimal;
    }

    public function getImagePrincipaleAttribute()
    {
        return $this->images()->where('est_principale', true)->first();
    }


     public function bonCommandeArticles()
    {
        return $this->hasMany(BonCommandeArticle::class);
    }

    public function lignesReception(): HasMany
{
    return $this->hasMany(LigneReception::class);
}
}