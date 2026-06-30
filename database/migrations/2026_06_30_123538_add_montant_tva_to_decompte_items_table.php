<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('decompte_items', function (Blueprint $table) {
            $table->decimal('montant_tva', 8, 2)->nullable()->after('taux_tva');
        });

        // Recalcule la TVA pour les decompte_items existants (montant_ht * taux_tva / 100)
        DB::table('decompte_items')->whereNull('montant_tva')->update([
            'montant_tva' => DB::raw('ROUND(montant_ht * taux_tva / 100, 2)'),
        ]);
    }

    public function down(): void
    {
        Schema::table('decompte_items', function (Blueprint $table) {
            $table->dropColumn('montant_tva');
        });
    }
};
