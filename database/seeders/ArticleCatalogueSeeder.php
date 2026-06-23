<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Categorie;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ArticleCatalogueSeeder extends Seeder
{
    public function run(): void
    {
        $articlesByCategory = [
            'VIA' => ['Viande bovine', 'Viande ovine', 'Foie', 'Abats'],
            'VOL' => ['Poulet', 'Dinde', 'Lapin', 'Oeufs'],
            'POI' => ['Sardine', 'Dorade', 'Merlan', 'Calamar', 'Crevette'],
            'EP' => ['Riz', 'Farine', 'Huile', 'Sucre', 'Conserve tomate'],
            'LEG' => ['Pomme de terre', 'Tomate', 'Carotte', 'Pomme', 'Orange'],
            'PAI' => ['Pain', 'Baguette', 'Croissant', 'Viennoiserie'],
        ];

        foreach ($articlesByCategory as $categoryCode => $articles) {
            $category = Categorie::query()->where('code', $categoryCode)->first();

            if (!$category) {
                continue;
            }

            foreach ($articles as $designation) {
                Article::query()->updateOrCreate(
                    ['reference' => $this->reference($categoryCode, $designation)],
                    [
                        'designation' => $designation,
                        'description' => null,
                        'categorie_id' => $category->id,
                        'unite_mesure' => in_array($designation, ['Oeufs', 'Pain', 'Baguette', 'Croissant', 'Viennoiserie'], true) ? 'piece' : 'kg',
                        'seuil_minimal' => 0,
                        'seuil_maximal' => 0,
                        'est_actif' => true,
                        'in_marche' => true,
                    ]
                );
            }
        }
    }

    private function reference(string $categoryCode, string $designation): string
    {
        return $categoryCode.'-'.Str::upper(Str::slug($designation, '-'));
    }
}
