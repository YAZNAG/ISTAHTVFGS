<?php
// app/Models/BonCommande.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BonCommande extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'reference', 'objet', 'description',
        'fournisseur_id', 'statut', 'categorie_id',
        'date_mise_ligne', 'date_limite_reception',
        'notes', 'created_by', 'pieces_jointes', 'date_debut', 'date_fin',
        'reason_annulation', 'annule_at'
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin' => 'date',
        'date_mise_ligne' => 'date',
        'date_limite_reception' => 'date',
        'annule_at' => 'datetime',
        'pieces_jointes' => 'array',
    ];

    // Constantes pour les statuts
    const STATUT_CREE = 'cree';
    const STATUT_ATTENTE_LIVRAISON = 'attente_livraison';
    const STATUT_LIVRE_COMPLETEMENT = 'livre_completement';
    const STATUT_LIVRE_PARTIELLEMENT = 'livre_partiellement';
    const STATUT_ANNULE = 'annule';

    public function categorie(): BelongsTo
    {
        return $this->belongsTo(Categorie::class, 'categorie_id', 'id');
    }

    public function articles()
    {
        return $this->hasMany(BonCommandeArticle::class);
    }

    public function decomptes(): HasMany
    {
        return $this->hasMany(Decompte::class, 'marche_id');
    }

    public function fournisseur(): BelongsTo
    {
        return $this->belongsTo(Fournisseur::class);
    }

    public function bonReceptions(): HasMany
    {
        return $this->hasMany(BonReception::class);
    }

    public function chefCommandes(): HasMany
    {
        return $this->hasMany(ChefCommande::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function historiqueStatuts(): HasMany
    {
        return $this->hasMany(HistoriqueStatutBc::class, 'bon_commande_id');
    }

    // Méthodes pour calculer les totaux
    public function getTotalTtcAttribute()
    {
        return $this->articles->sum('montant_ttc');
    }

    public function getTotalHtAttribute()
    {
        return $this->articles->sum('montant_ht');
    }

    public function getTotalTvaAttribute()
    {
        return $this->articles->sum('montant_tva');
    }

    public function scopeCurrent($query)
    {
        $today = today();
        return $query->whereDate('date_debut', '<=', $today)
                     ->whereDate('date_fin', '>=', $today);
    }

    // Méthode pour vérifier si la commande est complètement livrée
    public function estCompletementLivree(): bool
    {
        foreach ($this->articles as $ligneCommande) {
            $quantiteRecue = $this->getQuantiteRecuePourArticle($ligneCommande->article_id);
            if ($quantiteRecue < $ligneCommande->quantite_commandee) {
                return false;
            }
        }
        return true;
    }

    // Méthode pour vérifier si la commande est partiellement livrée
    public function estPartiellementLivree(): bool
    {
        $auMoinsUnArticleRecu = false;
        $auMoinsUnArticleManquant = false;

        foreach ($this->articles as $ligneCommande) {
            $quantiteRecue = $this->getQuantiteRecuePourArticle($ligneCommande->article_id);
            
            if ($quantiteRecue > 0) {
                $auMoinsUnArticleRecu = true;
            }
            if ($quantiteRecue < $ligneCommande->quantite_commandee) {
                $auMoinsUnArticleManquant = true;
            }
        }

        return $auMoinsUnArticleRecu && $auMoinsUnArticleManquant;
    }

    // Méthode pour obtenir la quantité reçue pour un article
   
    // Méthode pour obtenir le reste à recevoir pour un article
    public function getResteRecevoirPourArticle($articleId): float
    {
        $ligneCommande = $this->articles->firstWhere('article_id', $articleId);
        if (!$ligneCommande) {
            return 0;
        }

        $quantiteRecue = $this->getQuantiteRecuePourArticle($articleId);
        return max(0, $ligneCommande->quantite_commandee - $quantiteRecue);
    }

    // Méthode pour mettre à jour le statut basé sur les réceptions
    public function mettreAJourStatut(): void
    {
        if ($this->estCompletementLivree()) {
            $this->update(['statut' => self::STATUT_LIVRE_COMPLETEMENT]);
        } elseif ($this->estPartiellementLivree()) {
            $this->update(['statut' => self::STATUT_LIVRE_PARTIELLEMENT]);
        } else {
            $this->update(['statut' => self::STATUT_ATTENTE_LIVRAISON]);
        }
    }

    // Scope pour les commandes en attente de livraison
    public function scopeEnAttenteDeLivraison($query)
    {
        return $query->whereIn('statut', [self::STATUT_ATTENTE_LIVRAISON, self::STATUT_LIVRE_PARTIELLEMENT]);
    }

     // Méthode pour obtenir la quantité reçue pour un article (CORRIGÉE)
    public function getQuantiteRecuePourArticle($articleId): float
    {
        return $this->bonReceptions()
            ->with('lignesReception')
            ->get()
            ->flatMap(function($reception) {
                return $reception->lignesReception;
            })
            ->where('article_id', $articleId)
            ->sum('quantite_receptionnee');
    }

    // Méthode pour vérifier si la commande a des articles à recevoir
    public function aDesArticlesARecevoir(): bool
    {
        foreach ($this->articles as $ligneCommande) {
            $quantiteRecue = $this->getQuantiteRecuePourArticle($ligneCommande->article_id);
            if ($quantiteRecue < $ligneCommande->quantite_commandee) {
                return true;
            }
        }
        return false;
    }

    // Scope pour les commandes avec articles à recevoir
    public function scopeAvecArticlesARecevoir($query)
    {
        return $query->whereIn('statut', [self::STATUT_ATTENTE_LIVRAISON, self::STATUT_LIVRE_PARTIELLEMENT])
            ->whereHas('articles', function($query) {
                $query->whereRaw('quantite_commandee > (
                    SELECT COALESCE(SUM(lr.quantite_receptionnee), 0) 
                    FROM bon_receptions br 
                    JOIN ligne_receptions lr ON br.id = lr.bon_reception_id 
                    WHERE br.bon_commande_id = bon_commandes.id 
                    AND lr.article_id = bon_commande_articles.article_id
                )');
            });
    }

    protected $with = [];
}
