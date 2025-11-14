<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Categorie;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ArticlePoissonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categoryId = Categorie::where('code', 'POI')->firstOrFail()->id;

        
        $articles = [
[
                'reference' => 'POI1',
                'designation' => 'Anchoix frais',
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
                'reference' => 'POI2',
                'designation' => 'Calamar entier',
                'unite_mesure' => 'kg',
                'categorie_id' => $categoryId,
                'categorie_principale_id' => 1,
                'nature_prestation_id' => 1,
                'taux_tva' => 0,
                'seuil_minimal' => 0,
                'seuil_maximal' => 120,
                'est_actif' => 1,
                'in_marche' => 0,
            ],
[
                'reference' => 'POI3',
                'designation' => 'Chinchard',
                'unite_mesure' => 'kg',
                'categorie_id' => $categoryId,
                'categorie_principale_id' => 1,
                'nature_prestation_id' => 1,
                'taux_tva' => 0,
                'seuil_minimal' => 0,
                'seuil_maximal' => 100,
                'est_actif' => 1,
                'in_marche' => 0,
            ],
[
                'reference' => 'POI4',
                'designation' => 'Congre',
                'unite_mesure' => 'kg',
                'categorie_id' => $categoryId,
                'categorie_principale_id' => 1,
                'nature_prestation_id' => 1,
                'taux_tva' => 0,
                'seuil_minimal' => 0,
                'seuil_maximal' => 100,
                'est_actif' => 1,
                'in_marche' => 0,
            ],
[
                'reference' => 'POI5',
                'designation' => 'Crevettes décortiqués',
                'unite_mesure' => 'kg',
                'categorie_id' => $categoryId,
                'categorie_principale_id' => 1,
                'nature_prestation_id' => 1,
                'taux_tva' => 0,
                'seuil_minimal' => 0,
                'seuil_maximal' => 50,
                'est_actif' => 1,
                'in_marche' => 0,
            ],
[
                'reference' => 'POI6',
                'designation' => 'Crevettes non décortiqués',
                'unite_mesure' => 'kg',
                'categorie_id' => $categoryId,
                'categorie_principale_id' => 1,
                'nature_prestation_id' => 1,
                'taux_tva' => 0,
                'seuil_minimal' => 0,
                'seuil_maximal' => 140,
                'est_actif' => 1,
                'in_marche' => 0,
            ],
[
                'reference' => 'POI7',
                'designation' => 'Espadon en filet',
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
                'reference' => 'POI8',
                'designation' => 'Filet de colin',
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
[
                'reference' => 'POI9',
                'designation' => 'Merlan ration',
                'unite_mesure' => 'kg',
                'categorie_id' => $categoryId,
                'categorie_principale_id' => 1,
                'nature_prestation_id' => 1,
                'taux_tva' => 0,
                'seuil_minimal' => 0,
                'seuil_maximal' => 200,
                'est_actif' => 1,
                'in_marche' => 0,
            ],
[
                'reference' => 'POI10',
                'designation' => 'Ombrine ',
                'unite_mesure' => 'kg',
                'categorie_id' => $categoryId,
                'categorie_principale_id' => 1,
                'nature_prestation_id' => 1,
                'taux_tva' => 0,
                'seuil_minimal' => 0,
                'seuil_maximal' => 50,
                'est_actif' => 1,
                'in_marche' => 0,
            ],
[
                'reference' => 'POI11',
                'designation' => 'Sardine',
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
                'reference' => 'POI12',
                'designation' => 'Sar ration (250 gr)',
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
                'reference' => 'POI13',
                'designation' => 'Filet de Thon frais',
                'unite_mesure' => 'kg',
                'categorie_id' => $categoryId,
                'categorie_principale_id' => 1,
                'nature_prestation_id' => 1,
                'taux_tva' => 0,
                'seuil_minimal' => 0,
                'seuil_maximal' => 50,
                'est_actif' => 1,
                'in_marche' => 0,
            ],
        ];
        
    Article::insert($articles);
        
    }
}
