<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReturnStockItem extends Model
{
    protected $table = 'return_items';

    protected $fillable = [
        'return_id',
        'article_id',
        'quantite',
    ];

    public function return(): BelongsTo
    {
        return $this->belongsTo(ReturnStock::class, 'return_id');
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }
}
