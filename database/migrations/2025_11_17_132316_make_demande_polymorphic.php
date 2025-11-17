<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('demandes', function (Blueprint $table) {
            $table->dropForeign(['fiche_id']);
            $table->dropColumn('fiche_id');

            $table->morphs('demandable');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         Schema::table('demandes', function (Blueprint $table) {
            $table->dropMorphs('demandable');
            $table->foreignId('fiche_id')->constrained('fiches_techniques');
        });
    }
};
