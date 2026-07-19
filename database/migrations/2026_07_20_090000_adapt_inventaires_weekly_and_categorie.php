<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Retirer les contraintes UNIQUE devenues incompatibles avec l'hebdomadaire + par categorie
        $indexes = collect(DB::select('SHOW INDEX FROM inventaires'))->pluck('Key_name')->unique();

        Schema::table('inventaires', function (Blueprint $table) use ($indexes) {
            if ($indexes->contains('inventaires_mois_unique')) {
                $table->dropUnique('inventaires_mois_unique');
            }
            if ($indexes->contains('inventaires_semaine_unique')) {
                $table->dropUnique('inventaires_semaine_unique');
            }
        });

        // 2. mois devient facultatif (derive, plus de contrainte)
        Schema::table('inventaires', function (Blueprint $table) {
            $table->string('mois', 7)->nullable()->change();
        });

        // 3. Ajouter la categorie (nullable = inventaire "toutes categories")
        if (! Schema::hasColumn('inventaires', 'categorie_id')) {
            Schema::table('inventaires', function (Blueprint $table) {
                $table->foreignId('categorie_id')->nullable()->after('semaine')
                    ->constrained('categories')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::table('inventaires', function (Blueprint $table) {
            if (Schema::hasColumn('inventaires', 'categorie_id')) {
                $table->dropConstrainedForeignId('categorie_id');
            }
        });
    }
};
