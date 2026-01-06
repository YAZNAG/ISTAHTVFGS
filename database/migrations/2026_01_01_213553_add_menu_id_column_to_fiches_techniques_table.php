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
            $table->foreignId('menu_collectivite_id')->nullable()->constrained('menu_collectivites');
            $table->string('meal')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fiches_techniques', function (Blueprint $table) {
            $table->dropForeign(['menu_collectivite_id']);
            $table->dropColumn('menu_collectivite_id', 'meal');
        });
    }
};
