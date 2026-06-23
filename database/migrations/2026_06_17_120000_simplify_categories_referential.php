<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('categories', 'couleur')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->string('couleur', 20)->nullable()->after('nom');
            });
        }

        if (Schema::hasColumn('categories', 'couleur_affichage')) {
            DB::table('categories')
                ->where(function ($query) {
                    $query->whereNull('couleur')
                        ->orWhere('couleur', '');
                })
                ->update([
                    'couleur' => DB::raw("COALESCE(NULLIF(couleur_affichage, ''), '#155e9f')"),
                ]);
        }

        DB::table('categories')
            ->where(function ($query) {
                $query->whereNull('couleur')
                    ->orWhere('couleur', '');
            })
            ->update(['couleur' => '#155e9f']);

        $this->dropColumnsIfExist('categories', [
            'code_lot',
            'numero_lot',
            'couleur_affichage',
            'icone',
            'description',
        ]);
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            if (! Schema::hasColumn('categories', 'code_lot')) {
                $table->string('code_lot')->nullable()->after('code');
            }

            if (! Schema::hasColumn('categories', 'numero_lot')) {
                $table->integer('numero_lot')->nullable()->after('code_lot');
            }

            if (! Schema::hasColumn('categories', 'couleur_affichage')) {
                $table->string('couleur_affichage', 20)->nullable()->after('couleur');
            }

            if (! Schema::hasColumn('categories', 'icone')) {
                $table->string('icone')->nullable()->after('couleur_affichage');
            }

            if (! Schema::hasColumn('categories', 'description')) {
                $table->text('description')->nullable()->after('icone');
            }
        });

        if (Schema::hasColumn('categories', 'couleur_affichage')) {
            DB::table('categories')->update([
                'couleur_affichage' => DB::raw('couleur'),
            ]);
        }
    }

    private function dropColumnsIfExist(string $table, array $columns): void
    {
        $existingColumns = array_values(array_filter(
            $columns,
            fn (string $column) => Schema::hasColumn($table, $column)
        ));

        if ($existingColumns === []) {
            return;
        }

        Schema::table($table, function (Blueprint $table) use ($existingColumns) {
            $table->dropColumn($existingColumns);
        });
    }
};
