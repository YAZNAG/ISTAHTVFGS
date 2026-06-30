<?php

namespace App\Models;

use App\Models\Scopes\IsExistsInMarcheScope;
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
        'couleur_affichage',
        'quantite_stock',
        'unite_mesure',
        'taux_tva',
        'seuil_minimal', 
        'seuil_maximal', 
        'est_actif',
        'in_marche'
    ];

    protected $casts = [
        'est_actif' => 'boolean',
        'quantite_stock' => 'decimal:2',
        'taux_tva' => 'decimal:2',
        'seuil_minimal' => 'decimal:2',
        'seuil_maximal' => 'decimal:2',
    ];

    protected static function booted()
    {
        static::addGlobalScope(new IsExistsInMarcheScope);
    }

    public function resolveRouteBinding($value, $field = null)
{
    return $this->withNonExists()->where($field ?? $this->getRouteKeyName(), $value)->first();
}

    public function categorie(): BelongsTo
    {
        return $this->belongsTo(Categorie::class);
    }

    public function lignesEntreeStock()
    {
        return $this->hasMany(LigneEntreeStock::class, 'article_id', 'id');
    }

    public function mouvementsStock()
    {
        return $this->hasMany(MouvementStock::class, 'article_id', 'id');
    }

    public function mouvementsStockEntree()
    {
        return $this->hasMany(MouvementStock::class)
            ->where('type_mouvement', MouvementStock::TYPE_ENTREE);
    }

    public function mouvementsStockSortie()
    {
        return $this->hasMany(MouvementStock::class)
            ->where('type_mouvement', MouvementStock::TYPE_SORTIE);
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
        return $query->whereColumn('quantite_stock', '<=', 'seuil_minimal');
    }

    public function getEstEnRuptureAttribute()
    {
        return (float) $this->quantite_stock <= 0;
    }

    /**
     * Statut de stock unifié (seul endroit qui calcule cette règle dans l'app).
     * Stock faible : stock <= seuil_minimal * 0.8
     * Stock normal : stock > seuil_minimal
     * Zone intermédiaire (seuil*0.8 < stock <= seuil) traitée comme "faible".
     */
    public static function computeStockStatus(float $stock, float $seuilMinimal): array
    {
        if ($stock <= 0) {
            return ['label' => 'Rupture', 'type' => 'danger'];
        }

        if ($seuilMinimal > 0 && $stock <= $seuilMinimal * 0.8) {
            return ['label' => 'Stock faible', 'type' => 'warning'];
        }

        if ($seuilMinimal > 0 && $stock <= $seuilMinimal) {
            return ['label' => 'Stock faible', 'type' => 'warning'];
        }

        return ['label' => 'Stock normal', 'type' => 'success'];
    }

    public function getStatutStockAttribute(): array
    {
        return self::computeStockStatus(
            (float) ($this->quantite_stock ?? 0),
            (float) ($this->seuil_minimal ?? 0)
        );
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

    public function lignesSortieStock(): HasMany
{
    return $this->hasMany(LigneSortieStock::class);
}

    public function bonReceptionArticles(): HasMany
{
    return $this->hasMany(BonReceptionArticle::class);
}

    public function getPriceAttribute()
    {
        return BonCommandeArticle::where('article_id', $this->id)
            ->whereHas('bonCommande', function ($query) {
                $query->whereDate('date_debut', '<=', now())
                        ->whereDate('date_fin', '>=', now());
            })->first()?->prix_unitaire_ht;
    }

    public function currentBonCommandeArticle()
    {
        return $this->hasOne(BonCommandeArticle::class)
            ->whereHas('bonCommande', function ($q) {
                $q->whereDate('date_debut', '<=', today())
                ->whereDate('date_fin',   '>=', today());
            })->ofMany(['created_at' => 'max'], '>=');
    }

    /* opening stock : last movement BEFORE the month */
    public function lastMouvementBefore()
    {
        return $this->hasOne(MouvementStock::class)
            ->latest('date_mouvement');
    }

    public function entreesPeriode()
    {
        return $this->hasMany(MouvementStock::class)
            ->where('type_mouvement', MouvementStock::TYPE_ENTREE);
    }

    public function sortiesPeriode()
    {
        return $this->hasMany(MouvementStock::class)
            ->where('type_mouvement', MouvementStock::TYPE_SORTIE);
    }
}
