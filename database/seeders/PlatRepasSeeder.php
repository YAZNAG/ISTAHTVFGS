<?php

namespace Database\Seeders;

use App\Models\Repas;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PlatRepasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            'Hors d\'œuvres' => [
                'Asperges en sauce',
                'Salade noix chèvre chaud',
                'Quiche lorraine',
                'Cœurs d\'artichaud',
                'Assiette de charcuterie',
                'Salade de tomates',
                'Crevettes mayonnaise',
                'Soupe de poisson',
                'Soupe de légumes de saison',
                'Velouté de potiron',
                'Salade de brocolis',
                'Taboulé',
                'Œufs cocotte',
                'Croque-monsieur',
                'Salade de lentilles',
                'Salade de melon jambon',
                'Tielles sétoises',
                'Betterave rouge',
                'Gaspacho',
                'Rillettes',
            ],

            'Entrées' => [
                'Asperges en sauce',
                'Salade noix chèvre chaud',
                'Quiche lorraine',
                'Cœurs d\'artichaud',
                'Assiette de charcuterie',
                'Salade de tomates',
                'Crevettes mayonnaise',
                'Soupe de poisson',
                'Soupe de légumes de saison',
                'Velouté de potiron',
                'Salade de brocolis',
                'Taboulé',
                'Œufs cocotte',
                'Croque-monsieur',
                'Salade de lentilles',
                'Salade de melon jambon',
                'Tielles sétoises',
                'Betterave rouge',
                'Gaspacho',
                'Rillettes',
            ],

            'Plats' => [
                'Lasagnes aux légumes',
                'Tagliatelles à la carbonara',
                'Pâtes au jambon',
                'Spaghettis bolognaise',
                'Hachis parmentier',
                'Tartiflette',
                'Poulet pommes de terre',
                'Endives au jambon',
                'Pizza',
                'Escapoles milanaises',
                'Gratin d\'aubergines',
                'Bricks au fromage',
                'Omelette aux champignons',
                'Tarte aux légumes de saison',
                'Falafels',
                'Pommes de terre au four farcies fromage',
                'Boudin blanc',
                'Boudin noir',
                'Risotto',
                'Purée',
                'Cassoulet',
                'Nems riz cantonais',
                'Sushis maison',
                'Côtes de porc',
                'Gratin de choux fleur',
                'Gratin de coquillettes',
                'Hamburger maison',
                'Raviolis',
            ],

            'Plats Spéciaux pour invités' => [
                'Moules frites',
                'Saumon à la plancha',
                'Magret de canard',
                'Couscous',
                'Blanquette de veau',
                'Bœuf bourgignon',
                'Raclette',
                'Tomates farcies',
                'Fondue bourgignonne',
                'Filet mignon de porc à la moutarde',
                'Galettes au sarrasin',
                'Choucroute',
                'Paëlla',
                'Ratatouille',
                'Barbecue',
                'Gratin dauphinois',
                'Pot au feu',
                'Gigot d\'agneau',
                'Chili con carne',
                'Tortilla',
                'Tajine',
                'Pastilla de pigeon',
                'Cake aux olives',
                'Lasagnes de poisson',
                'Encornets farcis',
                'Cailles au miel',
                'Cassolette d\'escargots',
                'Civet',
                'Coq au vin',
                'Noix de Saint-Jacques',
                'Osso buco',
                'Paupiette de veau',
                'Queue de lotte',
                'Truites',
                'Tartines de légumes frais',
            ],
        ];

        foreach ($data as $repa_name => $plats) {
            $repas = Repas::create(['nom' => $repa_name]);
            foreach ($plats as $plat) {
                $repas->plats()->create(['nom' => $plat]);
            }
        }
    }
}
