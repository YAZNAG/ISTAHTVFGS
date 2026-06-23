<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['categorie_principales', 'nature_prestations', 'marche_categories'] as $table) {
            $this->archiveTable($table, 'archive_'.$table);
        }

        $this->dropForeignIfExists('articles', ['categorie_principale_id']);
        $this->dropForeignIfExists('articles', ['nature_prestation_id']);
        $this->dropForeignIfExists('articles', ['marche_category_id']);

        $this->dropForeignIfExists('categories', ['categorie_principale_id']);
        $this->dropForeignIfExists('categories', ['nature_prestation_id']);
        $this->dropIndexIfExists('categories', 'categories_code_categorie_principale_id_nature_prestation_id_unique');
        $this->dropIndexIfExists('categories', 'cat_code_cp_np_uniq');

        $this->dropForeignIfExists('bon_commandes', ['categorie_principale_id']);
        $this->dropForeignIfExists('bon_commandes', ['nature_prestation_id']);

        $this->dropColumnsIfExist('articles', [
            'marche_category_id',
            'legacy_marche_category_id',
            'categorie_principale_id',
            'nature_prestation_id',
        ]);

        $this->dropColumnsIfExist('categories', [
            'legacy_marche_category_id',
            'categorie_principale_id',
            'nature_prestation_id',
        ]);

        $this->dropColumnsIfExist('bon_commandes', [
            'legacy_marche_category_id',
            'categorie_principale_id',
            'nature_prestation_id',
        ]);

        $this->dropColumnsIfExist('chef_commandes', [
            'legacy_marche_category_id',
        ]);

        Schema::dropIfExists('marche_categories');
        Schema::dropIfExists('nature_prestations');
        Schema::dropIfExists('categorie_principales');
    }

    public function down(): void
    {
        foreach (['categorie_principales', 'nature_prestations', 'marche_categories'] as $table) {
            $this->restoreArchive($table, 'archive_'.$table);
        }

        Schema::table('categories', function (Blueprint $table) {
            if (!Schema::hasColumn('categories', 'legacy_marche_category_id')) {
                $table->unsignedBigInteger('legacy_marche_category_id')->nullable()->after('icone');
            }
            if (!Schema::hasColumn('categories', 'categorie_principale_id')) {
                $table->unsignedBigInteger('categorie_principale_id')->nullable()->after('description');
            }
            if (!Schema::hasColumn('categories', 'nature_prestation_id')) {
                $table->unsignedBigInteger('nature_prestation_id')->nullable()->after('categorie_principale_id');
            }
        });

        Schema::table('articles', function (Blueprint $table) {
            if (!Schema::hasColumn('articles', 'marche_category_id')) {
                $table->unsignedBigInteger('marche_category_id')->nullable()->after('categorie_id');
            }
            if (!Schema::hasColumn('articles', 'legacy_marche_category_id')) {
                $table->unsignedBigInteger('legacy_marche_category_id')->nullable()->after('marche_category_id');
            }
            if (!Schema::hasColumn('articles', 'categorie_principale_id')) {
                $table->unsignedBigInteger('categorie_principale_id')->nullable()->after('legacy_marche_category_id');
            }
            if (!Schema::hasColumn('articles', 'nature_prestation_id')) {
                $table->unsignedBigInteger('nature_prestation_id')->nullable()->after('categorie_principale_id');
            }
        });

        Schema::table('bon_commandes', function (Blueprint $table) {
            if (!Schema::hasColumn('bon_commandes', 'legacy_marche_category_id')) {
                $table->unsignedBigInteger('legacy_marche_category_id')->nullable()->after('categorie_id');
            }
            if (!Schema::hasColumn('bon_commandes', 'categorie_principale_id')) {
                $table->unsignedBigInteger('categorie_principale_id')->nullable()->after('description');
            }
            if (!Schema::hasColumn('bon_commandes', 'nature_prestation_id')) {
                $table->unsignedBigInteger('nature_prestation_id')->nullable()->after('categorie_principale_id');
            }
        });

        Schema::table('chef_commandes', function (Blueprint $table) {
            if (!Schema::hasColumn('chef_commandes', 'legacy_marche_category_id')) {
                $table->unsignedBigInteger('legacy_marche_category_id')->nullable()->after('categorie_id');
            }
        });
    }

    private function archiveTable(string $source, string $archive): void
    {
        if (!Schema::hasTable($source) || Schema::hasTable($archive)) {
            return;
        }

        DB::statement(sprintf(
            'CREATE TABLE `%s` AS SELECT * FROM `%s`',
            $archive,
            $source
        ));

        Schema::table($archive, function (Blueprint $table) use ($archive) {
            if (!Schema::hasColumn($archive, 'archived_at')) {
                $table->timestamp('archived_at')->nullable();
            }
        });

        DB::table($archive)->update(['archived_at' => now()]);
    }

    private function restoreArchive(string $target, string $archive): void
    {
        if (Schema::hasTable($target) || !Schema::hasTable($archive)) {
            return;
        }

        $columns = collect(Schema::getColumnListing($archive))
            ->reject(fn (string $column) => $column === 'archived_at')
            ->map(fn (string $column) => sprintf('`%s`', $column))
            ->implode(', ');

        DB::statement(sprintf(
            'CREATE TABLE `%s` AS SELECT %s FROM `%s`',
            $target,
            $columns,
            $archive
        ));
    }

    private function dropColumnsIfExist(string $table, array $columns): void
    {
        foreach ($columns as $column) {
            if (!Schema::hasColumn($table, $column)) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($column) {
                $blueprint->dropColumn($column);
            });
        }
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

    private function dropIndexIfExists(string $table, string $index): void
    {
        try {
            Schema::table($table, function (Blueprint $blueprint) use ($index) {
                $blueprint->dropUnique($index);
            });
        } catch (Throwable) {
            //
        }
    }
};
