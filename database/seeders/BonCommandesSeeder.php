<?php

namespace Database\Seeders;

use App\Models\BonCommande;
use App\Models\BonCommandeArticle;
use Illuminate\Database\Seeder;

class BonCommandesSeeder extends Seeder
{
    /**
     * Seed internal purchase orders with generated market-compatible lines.
     */
    public function run(): void
    {
        BonCommande::factory(20)
            ->has(BonCommandeArticle::factory(random_int(2, 5)), 'articles')
            ->create();
    }
}
