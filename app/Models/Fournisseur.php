<?php
// app/Models/Fournisseur.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Fournisseur extends Model implements HasMedia
{
    use SoftDeletes, HasFactory, InteractsWithMedia;

    protected $fillable = [
        'nom', 
        'raison_sociale',
        'contact',
        'telephone',
        'email',
        'adresse',
        'ville',
        'ice',
        'est_actif',
        'notes',
        'logo',
        'tp',
        'rc',
        'if',
        'cb',
        'cnss',
    ];

    protected $casts = [
        'est_actif' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function registerMediaCollections(): void
    {
        $this
            ->addMediaCollection('logo')
            ->singleFile();
    }

    public function bonCommandes(): HasMany
    {
        return $this->hasMany(BonCommande::class);
    }

    // Accessor pour l'URL du logo
    public function getLogoUrlAttribute()
    {
        if ($this->logo && Storage::disk('public')->exists($this->logo)) {
            return Storage::disk('public')->url($this->logo);
        }
        
        return null;
    }

    // Accessor pour obtenir le nom d'affichage (raison sociale ou nom)
    public function getNomAffichageAttribute()
    {
        return $this->raison_sociale ?: $this->nom;
    }

    // Méthode pour uploader le logo
    public function uploadLogo($file)
    {
        // Supprimer l'ancien logo s'il existe
        if ($this->logo && Storage::disk('public')->exists($this->logo)) {
            Storage::disk('public')->delete($this->logo);
        }

        // Stocker le nouveau logo
        $path = $file->store('fournisseurs/logos', 'public');
        $this->update(['logo' => $path]);
        
        return $path;
    }

    // Méthode scope pour les fournisseurs actifs
    public function scopeActif($query)
    {
        return $query->where('est_actif', true);
    }

    // Méthode pour désactiver le fournisseur
    public function desactiver()
    {
        $this->update(['est_actif' => false]);
    }

    // Méthode pour activer le fournisseur
    public function activer()
    {
        $this->update(['est_actif' => true]);
    }

    // Méthode pour vérifier si le fournisseur est actif
    public function estActif(): bool
    {
        return $this->est_actif === true;
    }

    // Méthode pour formater les informations de contact
    public function getContactCompletAttribute(): string
    {
        $contact = [];
        
        if ($this->contact) {
            $contact[] = $this->contact;
        }
        
        if ($this->telephone) {
            $contact[] = "Tél: " . $this->telephone;
        }
        
        if ($this->email) {
            $contact[] = "Email: " . $this->email;
        }
        
        return implode(' | ', $contact);
    }

    // Méthode pour formater l'adresse complète
    public function getAdresseCompleteAttribute(): string
    {
        $adresse = [];
        
        if ($this->adresse) {
            $adresse[] = $this->adresse;
        }
        
        if ($this->ville) {
            $adresse[] = $this->ville;
        }
        
        return implode(', ', $adresse);
    }
}