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
        Schema::create('ingredients', function (Blueprint $table) {
            $table->id();
            $table->decimal('prix_unitaire', 8, 2);
            $table->decimal('quantite', 8, 2);
            $table->decimal('taux_tva', 5, 2);
            $table->foreignId('fiche_id')->constrained('fiches_techniques')->cascadeOnDelete();
            $table->foreignId('article_id')->constrained('articles');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ingredients');
    }
};
