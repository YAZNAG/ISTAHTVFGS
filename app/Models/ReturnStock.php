<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReturnStock extends Model
{
    protected $table = 'returns';

    protected $fillable = [
        'numero',
        'returner_id',
        'receiver_id',
        'motif',
        'date',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public static function generateNumero(): string
    {
        $lastNumber = self::max('id') ?? 0;
        $numero = 'RE-' . str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        return $numero;
    }

    public function returner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'returner_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ReturnStockItem::class, 'return_id', 'id');
    }
}
