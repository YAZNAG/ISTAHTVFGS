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
        Schema::table('fiches_techniques', function (Blueprint $table) {
            $table->dropColumn(['nom', 'plat']);

            $table->foreignId('repas_id')->after('type')->constrained('repas');
            $table->foreignId('plat_id')->after('repas_id')->constrained('plats');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fiches_techniques', function (Blueprint $table) {
            $table->dropForeign(['repas_id']);
            $table->dropForeign(['plat_id']);

             $table->dropColumn(['nom_id', 'plat_id']);

            $table->string('nom');
            $table->string('plat');
        });
    }
};
