<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Restaurant extends Model
{
    protected $fillable = [
        'nom',
        'plat',
        'responsable',
        'effectif',
        'created_by',
    ];

    public function items()
    {
        return $this->hasMany(RestaurantItem::class);
    }

    public function responsable() 
    {
        return $this->belongsTo(User::class, 'responsable', 'id');
    }
}
