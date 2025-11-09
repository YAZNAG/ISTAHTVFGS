<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 50)->unique();
            $table->string('designation');
            $table->text('description')->nullable();
            $table->decimal('quantite_stock', 8, 2)->default(0);

            // FK vers tables cohérentes
            $table->foreignId('categorie_id')->constrained('categories')->cascadeOnDelete();
            $table->foreignId('categorie_principale_id')->constrained('categorie_principales')->cascadeOnDelete();
            $table->foreignId('nature_prestation_id')->constrained('nature_prestations')->cascadeOnDelete();

            $table->string('unite_mesure', 20)->default('UNITE');
            $table->decimal('taux_tva', 5, 2)->default(0);

            // Corrigé (typo “seuil”)
            $table->integer('seuil_minimal')->default(0);
            $table->integer('seuil_maximal')->default(0);

            $table->boolean('in_marche')->default(false);
            $table->boolean('est_actif')->default(true);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('articles');
    }
};
