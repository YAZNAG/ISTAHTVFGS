<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Categorie;
use App\Models\MarcheCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ArticlePainSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categoryId = Categorie::where('code', 'PAI')->firstOrFail()->id;
        $marche_category_id = MarcheCategory::where('nom', 'pain et gâteux')->firstOrFail()->id;
        
        $articles = [
[
                'reference' => 'PAI1',
                'designation' => 'Pain baguette de 180 g à 200 g',
                'unite_mesure' => 'Unité',
                'categorie_id' => $categoryId,
                'marche_category_id' => $marche_category_id,
                'categorie_principale_id' => 1,
                'nature_prestation_id' => 1,
                'taux_tva' => 0,
                'seuil_minimal' => 0,
                'seuil_maximal' => 40000,
                'est_actif' => 1,
                'in_marche' => 0,
            ],
[
                'reference' => 'PAI2',
                'designation' => 'Viennoiserie à base de farine pâtissière',
                'unite_mesure' => 'Unité',
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
[
                'reference' => 'PAI3',
                'designation' => 'Gâteaux au miel de 20 g (catégorie Chebakia, Mkharka etc)',
                'unite_mesure' => 'kg',
                'categorie_id' => $categoryId,
                'marche_category_id' => $marche_category_id,
                'categorie_principale_id' => 1,
                'nature_prestation_id' => 1,
                'taux_tva' => 0,
                'seuil_minimal' => 0,
                'seuil_maximal' => 80,
                'est_actif' => 1,
                'in_marche' => 0,
            ],
[
                'reference' => 'PAI4',
                'designation' => 'Dan up en flacon de 250 g ou similaire',
                'unite_mesure' => 'Flacon',
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
