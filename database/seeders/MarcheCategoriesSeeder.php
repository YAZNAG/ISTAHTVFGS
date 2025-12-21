<?php

namespace Database\Seeders;

use App\Models\MarcheCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MarcheCategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            'viandes et abats',
            'volailles et oeufs',
            'légumes et fruits',
            'épicerie,conserves et produits laitiers',
            'poissons et fruits de mer',
            'pain et gâteux',
            'quincaillerie et outillage',
            'fourniture de plomberie',
            'produits d\' entretien et détergents',
            'fourniture électrique',
            'fourniture de bureau',
        ];

        foreach ($data as $item) {
            MarcheCategory::create([
                'nom' => $item,
                'est_actif' => true
            ]);
        }
    }
}