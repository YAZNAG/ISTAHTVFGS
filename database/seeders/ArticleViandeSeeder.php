<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Categorie;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ArticleViandeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categoryId = Categorie::where('code', 'VIA')->firstOrFail()->id;

        
        $articles = [
[
        'reference' => 'VIA1',
        'designation' => 'Cervelle de bœuf de 300 à 400 gr',
        'unite_mesure' => 'pc',
        'categorie_id' => $categoryId,
        'categorie_principale_id' => 1,
        'nature_prestation_id' => 1,
        'taux_tva' => 0,
        'seuil_minimal' => 0,
        'seuil_maximal' => 20,
        'est_actif' => 1,
        'in_marche' => 0,
    ],
[
        'reference' => 'VIA2',
        'designation' => 'Contre filet de bœuf ',
        'unite_mesure' => 'kg',
        'categorie_id' => $categoryId,
        'categorie_principale_id' => 1,
        'nature_prestation_id' => 1,
        'taux_tva' => 0,
        'seuil_minimal' => 0,
        'seuil_maximal' => 30,
        'est_actif' => 1,
        'in_marche' => 0,
    ],
[
        'reference' => 'VIA3',
        'designation' => 'Cuise de bœuf desossé',
        'unite_mesure' => 'kg',
        'categorie_id' => $categoryId,
        'categorie_principale_id' => 1,
        'nature_prestation_id' => 1,
        'taux_tva' => 0,
        'seuil_minimal' => 0,
        'seuil_maximal' => 3000,
        'est_actif' => 1,
        'in_marche' => 0,
    ],
[
        'reference' => 'VIA4',
        'designation' => 'Cuise de bœuf aloyau',
        'unite_mesure' => 'kg',
        'categorie_id' => $categoryId,
        'categorie_principale_id' => 1,
        'nature_prestation_id' => 1,
        'taux_tva' => 0,
        'seuil_minimal' => 0,
        'seuil_maximal' => 300,
        'est_actif' => 1,
        'in_marche' => 0,
    ],
[
        'reference' => 'VIA5',
        'designation' => 'Filet de bœuf paré ',
        'unite_mesure' => 'kg',
        'categorie_id' => $categoryId,
        'categorie_principale_id' => 1,
        'nature_prestation_id' => 1,
        'taux_tva' => 0,
        'seuil_minimal' => 0,
        'seuil_maximal' => 20,
        'est_actif' => 1,
        'in_marche' => 0,
    ],
[
        'reference' => 'VIA6',
        'designation' => 'Foie de bœuf ',
        'unite_mesure' => 'kg',
        'categorie_id' => $categoryId,
        'categorie_principale_id' => 1,
        'nature_prestation_id' => 1,
        'taux_tva' => 0,
        'seuil_minimal' => 0,
        'seuil_maximal' => 150,
        'est_actif' => 1,
        'in_marche' => 0,
    ],
[
        'reference' => 'VIA7',
        'designation' => 'Gigot de mouton ',
        'unite_mesure' => 'kg',
        'categorie_id' => $categoryId,
        'categorie_principale_id' => 1,
        'nature_prestation_id' => 1,
        'taux_tva' => 0,
        'seuil_minimal' => 0,
        'seuil_maximal' => 30,
        'est_actif' => 1,
        'in_marche' => 0,
    ],
[
        'reference' => 'VIA8',
        'designation' => 'Saucisse de bœuf',
        'unite_mesure' => 'kg',
        'categorie_id' => $categoryId,
        'categorie_principale_id' => 1,
        'nature_prestation_id' => 1,
        'taux_tva' => 0,
        'seuil_minimal' => 0,
        'seuil_maximal' => 150,
        'est_actif' => 1,
        'in_marche' => 0,
    ],
[
        'reference' => 'VIA9',
        'designation' => 'Mouton entier',
        'unite_mesure' => 'kg',
        'categorie_id' => $categoryId,
        'categorie_principale_id' => 1,
        'nature_prestation_id' => 1,
        'taux_tva' => 0,
        'seuil_minimal' => 0,
        'seuil_maximal' => 500,
        'est_actif' => 1,
        'in_marche' => 0,
    ],
];

    Article::insert($articles);

    }
}
