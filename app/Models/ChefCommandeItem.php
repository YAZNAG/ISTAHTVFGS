<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChefCommandeItem extends Model
{
    public $table = 'chef_commandes_items';
    public $fillable = [
        'chef_commande_id',
        'article_id',
        'quantite_commandee',
    ];


    public function article()
    {
        return $this->belongsTo(Article::class);
    }

    public function chefCommande()
    {
        return $this->belongsTo(ChefCommande::class);
    }

    public function articles() 
    {
        return $this->belongsTo(Article::class);
    }

}
