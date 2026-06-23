<?php

namespace Database\Seeders;

use App\Models\Categorie;
use Illuminate\Database\Seeder;

class CategoriesSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['code' => 'VIA', 'nom' => 'Viandes et Abats', 'couleur' => '#0f3b63'],
            ['code' => 'VOL', 'nom' => 'Volailles, Lapin et Oeufs', 'couleur' => '#155e9f'],
            ['code' => 'POI', 'nom' => 'Poissons et Fruits de Mer', 'couleur' => '#0ea5b7'],
            ['code' => 'LEG', 'nom' => 'Fruits et Legumes', 'couleur' => '#15803d'],
            ['code' => 'EP', 'nom' => 'Epicerie', 'couleur' => '#64748b'],
            ['code' => 'PAI', 'nom' => 'Pain et Viennoiserie', 'couleur' => '#f59e0b'],
            ['code' => 'ENT', 'nom' => 'Produits d entretien', 'couleur' => '#dc2626'],
            ['code' => 'DIV', 'nom' => 'Fournitures diverses', 'couleur' => '#475569'],
        ];

        foreach ($categories as $category) {
            Categorie::updateOrCreate(
                ['code' => $category['code']],
                [
                    'nom' => $category['nom'],
                    'couleur' => $category['couleur'],
                    'est_actif' => true,
                ]
            );
        }
    }
}
