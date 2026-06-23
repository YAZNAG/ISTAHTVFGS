<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            if (!Schema::hasColumn('categories', 'couleur')) {
                $table->string('couleur', 20)->nullable()->after('description');
            }

            if (!Schema::hasColumn('categories', 'legacy_marche_category_id')) {
                $table->unsignedBigInteger('legacy_marche_category_id')->nullable()->after('icone');
            }
        });

        Schema::table('articles', function (Blueprint $table) {
            if (!Schema::hasColumn('articles', 'legacy_marche_category_id')) {
                $table->unsignedBigInteger('legacy_marche_category_id')->nullable()->after('marche_category_id');
            }
        });

        Schema::table('bon_commandes', function (Blueprint $table) {
            if (!Schema::hasColumn('bon_commandes', 'legacy_marche_category_id')) {
                $table->unsignedBigInteger('legacy_marche_category_id')->nullable()->after('categorie_id');
            }
        });

        Schema::table('chef_commandes', function (Blueprint $table) {
            if (!Schema::hasColumn('chef_commandes', 'legacy_marche_category_id')) {
                $table->unsignedBigInteger('legacy_marche_category_id')->nullable()->after('categorie_id');
            }
        });

        Schema::table('bon_commande_articles', function (Blueprint $table) {
            if (!Schema::hasColumn('bon_commande_articles', 'quantite_minimale')) {
                $table->decimal('quantite_minimale', 8, 2)->default(0)->after('article_id');
            }

            if (!Schema::hasColumn('bon_commande_articles', 'quantite_maximale')) {
                $table->decimal('quantite_maximale', 8, 2)->nullable()->after('quantite_minimale');
            }
        });

        DB::table('categories')
            ->whereNull('couleur')
            ->update(['couleur' => DB::raw('couleur_affichage')]);

        DB::table('articles')
            ->whereNull('legacy_marche_category_id')
            ->whereNotNull('marche_category_id')
            ->update(['legacy_marche_category_id' => DB::raw('marche_category_id')]);

        DB::table('bon_commandes')
            ->whereNull('legacy_marche_category_id')
            ->update(['legacy_marche_category_id' => DB::raw('categorie_id')]);

        DB::table('chef_commandes')
            ->whereNull('legacy_marche_category_id')
            ->update(['legacy_marche_category_id' => DB::raw('categorie_id')]);

        $mapping = $this->ensureBusinessCategories();

        $this->dropForeignIfExists('bon_commandes', ['categorie_id']);
        $this->dropForeignIfExists('chef_commandes', ['categorie_id']);

        foreach ($mapping as $legacyMarcheCategoryId => $categoryId) {
            DB::table('bon_commandes')
                ->where('legacy_marche_category_id', $legacyMarcheCategoryId)
                ->update(['categorie_id' => $categoryId]);

            DB::table('chef_commandes')
                ->where('legacy_marche_category_id', $legacyMarcheCategoryId)
                ->update(['categorie_id' => $categoryId]);

            DB::table('categories')
                ->where('id', $categoryId)
                ->update(['legacy_marche_category_id' => $legacyMarcheCategoryId]);
        }

        DB::table('bon_commande_articles')
            ->whereNull('quantite_maximale')
            ->update([
                'quantite_minimale' => 0,
                'quantite_maximale' => DB::raw('quantite_commandee'),
            ]);

        Schema::table('bon_commandes', function (Blueprint $table) {
            $table->foreign('categorie_id')->references('id')->on('categories');
        });

        Schema::table('chef_commandes', function (Blueprint $table) {
            $table->foreign('categorie_id')->references('id')->on('categories');
        });
    }

    public function down(): void
    {
        $this->dropForeignIfExists('bon_commandes', ['categorie_id']);
        $this->dropForeignIfExists('chef_commandes', ['categorie_id']);

        DB::table('bon_commandes')
            ->whereNotNull('legacy_marche_category_id')
            ->update(['categorie_id' => DB::raw('legacy_marche_category_id')]);

        DB::table('chef_commandes')
            ->whereNotNull('legacy_marche_category_id')
            ->update(['categorie_id' => DB::raw('legacy_marche_category_id')]);

        Schema::table('bon_commandes', function (Blueprint $table) {
            $table->foreign('categorie_id')->references('id')->on('marche_categories');
        });

        Schema::table('chef_commandes', function (Blueprint $table) {
            $table->foreign('categorie_id')->references('id')->on('marche_categories');
        });

        Schema::table('bon_commande_articles', function (Blueprint $table) {
            foreach (['quantite_maximale', 'quantite_minimale'] as $column) {
                if (Schema::hasColumn('bon_commande_articles', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('chef_commandes', function (Blueprint $table) {
            if (Schema::hasColumn('chef_commandes', 'legacy_marche_category_id')) {
                $table->dropColumn('legacy_marche_category_id');
            }
        });

        Schema::table('bon_commandes', function (Blueprint $table) {
            if (Schema::hasColumn('bon_commandes', 'legacy_marche_category_id')) {
                $table->dropColumn('legacy_marche_category_id');
            }
        });

        Schema::table('articles', function (Blueprint $table) {
            if (Schema::hasColumn('articles', 'legacy_marche_category_id')) {
                $table->dropColumn('legacy_marche_category_id');
            }
        });

        Schema::table('categories', function (Blueprint $table) {
            foreach (['legacy_marche_category_id', 'couleur'] as $column) {
                if (Schema::hasColumn('categories', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    private function ensureBusinessCategories(): array
    {
        $mapping = [];
        $mainCategoryId = Schema::hasTable('categorie_principales')
            ? DB::table('categorie_principales')->value('id')
            : null;
        $natureId = Schema::hasTable('nature_prestations')
            ? DB::table('nature_prestations')->value('id')
            : null;

        foreach (DB::table('marche_categories')->get(['id', 'nom', 'est_actif']) as $legacyCategory) {
            $category = $this->findMatchingCategory($legacyCategory->nom);

            if (!$category) {
                $payload = [
                    'nom' => $legacyCategory->nom,
                    'code' => Str::upper(Str::limit(Str::slug($legacyCategory->nom, ''), 20, '')),
                    'description' => null,
                    'est_actif' => (bool) $legacyCategory->est_actif,
                    'couleur' => '#00AEEF',
                    'couleur_affichage' => '#00AEEF',
                    'icone' => 'Squares2X2Icon',
                    'legacy_marche_category_id' => $legacyCategory->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                if (Schema::hasColumn('categories', 'categorie_principale_id')) {
                    $payload['categorie_principale_id'] = $mainCategoryId ?? 1;
                }

                if (Schema::hasColumn('categories', 'nature_prestation_id')) {
                    $payload['nature_prestation_id'] = $natureId ?? 1;
                }

                $categoryId = DB::table('categories')->insertGetId($payload);
            } else {
                $categoryId = $category->id;
            }

            $mapping[$legacyCategory->id] = $categoryId;
        }

        return $mapping;
    }

    private function findMatchingCategory(string $legacyName): ?object
    {
        $legacy = $this->normalize($legacyName);

        return DB::table('categories')
            ->get(['id', 'nom', 'code', 'code_lot', 'numero_lot'])
            ->first(function ($category) use ($legacy) {
                $categoryName = $this->normalize($category->nom);

                return str_contains($categoryName, $legacy)
                    || str_contains($legacy, $categoryName)
                    || $this->sameKnownFoodCategory($legacy, $categoryName);
            });
    }

    private function sameKnownFoodCategory(string $legacy, string $category): bool
    {
        $needles = [
            ['viande', 'viande'],
            ['volaille', 'volaille'],
            ['poisson', 'poisson'],
            ['epicerie', 'epicerie'],
            ['fruit legume', 'fruit legume'],
            ['pain', 'pain'],
            ['entretien', 'entretien'],
        ];

        foreach ($needles as [$left, $right]) {
            if (str_contains($legacy, $left) && str_contains($category, $right)) {
                return true;
            }
        }

        return false;
    }

    private function normalize(string $value): string
    {
        return Str::of($value)
            ->lower()
            ->ascii()
            ->replaceMatches('/[^a-z0-9]+/', ' ')
            ->squish()
            ->toString();
    }

    private function dropForeignIfExists(string $table, array $columns): void
    {
        try {
            Schema::table($table, function (Blueprint $blueprint) use ($columns) {
                $blueprint->dropForeign($columns);
            });
        } catch (Throwable) {
            //
        }
    }
};
