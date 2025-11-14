<?php

namespace Database\Seeders;

use App\Models\Categorie;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Populates the categories table with initial data.
     */
    public function run()
    {
        $categories = [
            [
                'nom' => 'légume',
                'code' => 'LEG',
                'description' => '',
                'categorie_principale_id' => 1,
                'nature_prestation_id' => 1,
                'est_actif' => 1,
            ],
            [
                'nom' => 'épiserie',
                'code' => 'EP',
                'description' => '',
                'categorie_principale_id' => 1,
                'nature_prestation_id' => 1,
                'est_actif' => 1,
            ],
            [
                'nom' => 'viandes',
                'code' => 'VIA',
                'description' => '',
                'categorie_principale_id' => 1,
                'nature_prestation_id' => 1,
                'est_actif' => 1,
            ],
            [
                'nom' => 'volailles',
                'code' => 'VOL',
                'description' => '',
                'categorie_principale_id' => 1,
                'nature_prestation_id' => 1,
                'est_actif' => 1,
            ],
            [
                'nom' => 'poissons',
                'code' => 'POI',
                'description' => '',
                'categorie_principale_id' => 1,
                'nature_prestation_id' => 1,
                'est_actif' => 1,
            ],
            [
                'nom' => 'fruits',
                'code' => 'FRU',
                'description' => null,
                'categorie_principale_id' => 1,
                'nature_prestation_id' => 1,
                'est_actif' => 1,
            ],
            [
                'nom' => 'pain',
                'code' => 'PAI',
                'description' => null,
                'categorie_principale_id' => 1,
                'nature_prestation_id' => 1,
                'est_actif' => 1,
            ]
        ];

        foreach ($categories as $category) {
            Categorie::create($category);
        }
    }
}