<?php

namespace Database\Seeders;

use Database\Seeders\Permissions\ApplicationRolesSeeder;
use Database\Seeders\Permissions\ArticlePermissionSeeder;
use Database\Seeders\Permissions\BonLivraisonPermissionSeeder;
use Database\Seeders\Permissions\BonReceptionPermissionSeeder;
use Database\Seeders\Permissions\BonSortiePermissionSeeder;
use Database\Seeders\Permissions\ChefCommandePermissionSeeder;
use Database\Seeders\Permissions\DemandePermissionSeeder;
use Database\Seeders\Permissions\FicheTechniquePermissionSeeder;
use Database\Seeders\Permissions\FournisseurPermissionSeeder;
use Database\Seeders\Permissions\MarchePermissionSeeder;
use Database\Seeders\Permissions\RaportPermissionSeeder;
use Database\Seeders\Permissions\RestaurantPermissionSeeder;
use Database\Seeders\Permissions\RolePermissionSeeder;
use Database\Seeders\Permissions\StockPermissionSeeder;
use Database\Seeders\Permissions\UserPermissionSeeder;
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
            // ArticleEpicerieSeeder::class,
            ArticleLegumeSeeder::class,
            // ArticleViandeSeeder::class,
            // ArticlePoissonSeeder::class,
            // ArticlePainSeeder::class,
            // ArticleFruitSeeder::class,
            // ArticleVolailleSeeder::class,
            FournisseursSeeder::class,
            // BonCommandesSeeder::class,
            // BonCommandeArticlesSeeder::class,
            // BonReceptionsSeeder::class,
            // LigneReceptionsSeeder::class,
            // EntreeStocksSeeder::class,
            // LigneEntreeStocksSeeder::class,
            // HistoriqueStatutBcsSeeder::class,
            ApplicationRolesSeeder::class,
            ArticlePermissionSeeder::class,
            ChefCommandePermissionSeeder::class,
            FournisseurPermissionSeeder::class,
            MarchePermissionSeeder::class,
            BonLivraisonPermissionSeeder::class,
            BonReceptionPermissionSeeder::class,
            StockPermissionSeeder::class,
            DemandePermissionSeeder::class,
            BonSortiePermissionSeeder::class,
            RestaurantPermissionSeeder::class,
            FicheTechniquePermissionSeeder::class,
            UserPermissionSeeder::class,
            RaportPermissionSeeder::class,
            RolePermissionSeeder::class,
            MarchePermissionSeeder::class,

        ]);
    }
}