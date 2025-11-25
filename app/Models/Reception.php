<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Reception extends Model implements HasMedia
{
    use InteractsWithMedia, SoftDeletes;

    protected $with = ['media', 'bonLivraison.items'];
    
    protected $fillable = [
        'numero',
        'bon_livraison_id',
    ];

    public function registerMediaCollections(?Media $media = null): void
    {
        $this
            ->addMediaCollection('bon')
            ->singleFile();
    }

    public static function genererNumero()
    {
        $lastNumber = (int) self::withTrashed()->max('id') ?? 0;
        $numero = 'BR-' . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        return $numero;
    }

    public function bonLivraison()
    {
        return $this->belongsTo(BonLivraison::class);
    }

    public function mouvements()
    {
        return $this->morphMany(MouvementStock::class, 'referenceable');
    }
}
