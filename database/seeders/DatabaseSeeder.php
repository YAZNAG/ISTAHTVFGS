<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run()
    {
        $this->call([
            UsersSeeder::class,
            CategoriePrincipalesSeeder::class,
            NaturePrestationsSeeder::class,
            CategoriesSeeder::class,
            // ArticlesSeeder::class,
            // ArticleImagesSeeder::class,
            ArticleEpicerieSeeder::class,
            ArticleLegumeSeeder::class,
            ArticleViandeSeeder::class,
            ArticlePoissonSeeder::class,
            ArticlePainSeeder::class,
            ArticleFruitSeeder::class,
            ArticleVolailleSeeder::class,
            FournisseursSeeder::class,
            // BonCommandesSeeder::class,
            // BonCommandeArticlesSeeder::class,
            // BonReceptionsSeeder::class,
            // LigneReceptionsSeeder::class,
            // EntreeStocksSeeder::class,
            // LigneEntreeStocksSeeder::class,
            // HistoriqueStatutBcsSeeder::class,
        ]);
    }
}