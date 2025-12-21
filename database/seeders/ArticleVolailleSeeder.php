<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Categorie;
use App\Models\MarcheCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ArticleVolailleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categoryId = Categorie::where('code', 'VOL')->firstOrFail()->id;
        $marche_category_id = MarcheCategory::where('nom', 'volailles et oeufs')->firstOrFail()->id;
        
        $articles = [
[
        'reference' => 'VOL1',
        'designation' => 'Coqulet de 400 à 500 gr',
        'unite_mesure' => 'kg',
        'categorie_id' => $categoryId,
        'marche_category_id' => $marche_category_id,
        'categorie_principale_id' => 1,
        'nature_prestation_id' => 1,
        'taux_tva' => 0,
        'seuil_minimal' => 0,
        'seuil_maximal' => 20,
        'est_actif' => 1,
        'in_marche' => 0,
    ],
[
        'reference' => 'VOL2',
        'designation' => 'filet de dinde ',
        'unite_mesure' => 'kg',
        'categorie_id' => $categoryId,
        'marche_category_id' => $marche_category_id,
        'categorie_principale_id' => 1,
        'nature_prestation_id' => 1,
        'taux_tva' => 0,
        'seuil_minimal' => 0,
        'seuil_maximal' => 1800,
        'est_actif' => 1,
        'in_marche' => 0,
    ],
[
        'reference' => 'VOL3',
        'designation' => 'Œuf de caille paquet de 18 u',
        'unite_mesure' => 'pt',
        'categorie_id' => $categoryId,
        'marche_category_id' => $marche_category_id,
        'categorie_principale_id' => 1,
        'nature_prestation_id' => 1,
        'taux_tva' => 0,
        'seuil_minimal' => 0,
        'seuil_maximal' => 10,
        'est_actif' => 1,
        'in_marche' => 0,
    ],
[
        'reference' => 'VOL4',
        'designation' => 'Œufs L (0,63/73 g)',
        'unite_mesure' => 'pce',
        'categorie_id' => $categoryId,
        'marche_category_id' => $marche_category_id,
        'categorie_principale_id' => 1,
        'nature_prestation_id' => 1,
        'taux_tva' => 0,
        'seuil_minimal' => 0,
        'seuil_maximal' => 30000,
        'est_actif' => 1,
        'in_marche' => 0,
    ],
[
        'reference' => 'VOL5',
        'designation' => 'pigeon entier vidé ',
        'unite_mesure' => 'pce',
        'categorie_id' => $categoryId,
        'marche_category_id' => $marche_category_id,
        'categorie_principale_id' => 1,
        'nature_prestation_id' => 1,
        'taux_tva' => 0,
        'seuil_minimal' => 0,
        'seuil_maximal' => 20,
        'est_actif' => 1,
        'in_marche' => 0,
    ],
[
        'reference' => 'VOL6',
        'designation' => 'Poulet d\'élevage vidé de 1,200 kg',
        'unite_mesure' => 'kg',
        'categorie_id' => $categoryId,
        'marche_category_id' => $marche_category_id,
        'categorie_principale_id' => 1,
        'nature_prestation_id' => 1,
        'taux_tva' => 0,
        'seuil_minimal' => 0,
        'seuil_maximal' => 4000,
        'est_actif' => 1,
        'in_marche' => 0,
    ],
];

    Article::insert($articles);

    }
}
