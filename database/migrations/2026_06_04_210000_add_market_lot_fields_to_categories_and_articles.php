<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            if (!Schema::hasColumn('categories', 'code_lot')) {
                $table->string('code_lot', 20)->nullable()->after('code');
            }

            if (!Schema::hasColumn('categories', 'numero_lot')) {
                $table->unsignedTinyInteger('numero_lot')->nullable()->after('code_lot');
            }

            if (!Schema::hasColumn('categories', 'couleur_affichage')) {
                $table->string('couleur_affichage', 20)->nullable()->after('numero_lot');
            }

            if (!Schema::hasColumn('categories', 'icone')) {
                $table->string('icone', 80)->nullable()->after('couleur_affichage');
            }
        });

        Schema::table('articles', function (Blueprint $table) {
            if (!Schema::hasColumn('articles', 'code_lot')) {
                $table->string('code_lot', 20)->nullable()->after('marche_category_id');
            }

            if (!Schema::hasColumn('articles', 'numero_lot')) {
                $table->unsignedTinyInteger('numero_lot')->nullable()->after('code_lot');
            }

            if (!Schema::hasColumn('articles', 'couleur_affichage')) {
                $table->string('couleur_affichage', 20)->nullable()->after('numero_lot');
            }

            if (!Schema::hasColumn('articles', 'icone')) {
                $table->string('icone', 80)->nullable()->after('couleur_affichage');
            }
        });

        $lots = [
            'VIA' => [
                'nom' => 'Lot N°1 - Viandes et Abats',
                'description' => 'Articles du marché Viandes et Abats : bœuf, mouton, abats et découpes associées.',
                'code_lot' => 'LOT-1',
                'numero_lot' => 1,
                'couleur_affichage' => '#0b2f5f',
                'icone' => 'ScaleIcon',
                'marche_category_id' => 1,
                'marche_nom' => 'Viandes et Abats',
            ],
            'VOL' => [
                'nom' => 'Lot N°2 - Volailles, Lapin et Œufs',
                'description' => 'Articles du marché Volailles, Lapin et Œufs : poulet, dinde, canard, lapin et œufs.',
                'code_lot' => 'LOT-2',
                'numero_lot' => 2,
                'couleur_affichage' => '#155e9f',
                'icone' => 'Squares2X2Icon',
                'marche_category_id' => 2,
                'marche_nom' => 'Volailles, Lapin et Œufs',
            ],
            'POI' => [
                'nom' => 'Lot N°3 - Poissons et Fruits de Mer',
                'description' => 'Articles du marché Poissons et Fruits de Mer : poissons frais, filets, crustacés et fruits de mer.',
                'code_lot' => 'LOT-3',
                'numero_lot' => 3,
                'couleur_affichage' => '#0ea5b7',
                'icone' => 'GlobeAltIcon',
                'marche_category_id' => 5,
                'marche_nom' => 'Poissons et Fruits de Mer',
            ],
            'EP' => [
                'nom' => 'Lot N°4 - Épicerie',
                'description' => 'Articles du marché Épicerie : conserves, produits laitiers, condiments, féculents et denrées sèches.',
                'code_lot' => 'LOT-4',
                'numero_lot' => 4,
                'couleur_affichage' => '#15803d',
                'icone' => 'ShoppingBagIcon',
                'marche_category_id' => 4,
                'marche_nom' => 'Épicerie',
            ],
            'LEG' => [
                'nom' => 'Lot N°5 - Fruits et Légumes',
                'description' => 'Articles du marché Fruits et Légumes : légumes frais, fruits frais, herbes et produits végétaux.',
                'code_lot' => 'LOT-5',
                'numero_lot' => 5,
                'couleur_affichage' => '#f59e0b',
                'icone' => 'SunIcon',
                'marche_category_id' => 3,
                'marche_nom' => 'Fruits et Légumes',
            ],
            'PAI' => [
                'nom' => 'Lot N°6 - Pain et Viennoiserie',
                'description' => 'Articles du marché Pain et Viennoiserie : pains, viennoiseries et produits de boulangerie.',
                'code_lot' => 'LOT-6',
                'numero_lot' => 6,
                'couleur_affichage' => '#ea580c',
                'icone' => 'CakeIcon',
                'marche_category_id' => 6,
                'marche_nom' => 'Pain et Viennoiserie',
            ],
        ];

        $mainCategoryId = DB::table('categorie_principales')->value('id');
        $natureId = DB::table('nature_prestations')->value('id');

        if (!$mainCategoryId || !$natureId) {
            return;
        }

        foreach ($lots as $code => $lot) {
            DB::table('categories')->updateOrInsert(
                ['code' => $code],
                [
                    'nom' => $lot['nom'],
                    'description' => $lot['description'],
                    'categorie_principale_id' => $mainCategoryId,
                    'nature_prestation_id' => $natureId,
                    'est_actif' => true,
                    'code_lot' => $lot['code_lot'],
                    'numero_lot' => $lot['numero_lot'],
                    'couleur_affichage' => $lot['couleur_affichage'],
                    'icone' => $lot['icone'],
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );

            DB::table('marche_categories')
                ->where('id', $lot['marche_category_id'])
                ->update([
                    'nom' => $lot['marche_nom'],
                    'est_actif' => true,
                    'updated_at' => now(),
                ]);
        }

        $fruitCategoryId = DB::table('categories')->where('code', 'FRU')->value('id');
        $fruitLegumeCategoryId = DB::table('categories')->where('code', 'LEG')->value('id');

        if ($fruitCategoryId && $fruitLegumeCategoryId && $fruitCategoryId !== $fruitLegumeCategoryId) {
            DB::table('articles')
                ->where('categorie_id', $fruitCategoryId)
                ->update(['categorie_id' => $fruitLegumeCategoryId]);

            DB::table('categories')->where('id', $fruitCategoryId)->delete();
        }

        $categories = DB::table('categories')
            ->whereNotNull('code_lot')
            ->get(['id', 'code_lot', 'numero_lot', 'couleur_affichage', 'icone']);

        foreach ($categories as $categorie) {
            DB::table('articles')
                ->where('categorie_id', $categorie->id)
                ->update([
                    'code_lot' => $categorie->code_lot,
                    'numero_lot' => $categorie->numero_lot,
                    'couleur_affichage' => $categorie->couleur_affichage,
                    'icone' => $categorie->icone,
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            foreach (['icone', 'couleur_affichage', 'numero_lot', 'code_lot'] as $column) {
                if (Schema::hasColumn('articles', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('categories', function (Blueprint $table) {
            foreach (['icone', 'couleur_affichage', 'numero_lot', 'code_lot'] as $column) {
                if (Schema::hasColumn('categories', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
