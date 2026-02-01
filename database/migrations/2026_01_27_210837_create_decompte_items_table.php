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
        Schema::create('decompte_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('decompte_id')->constrained();
            $table->foreignId('article_id')->constrained();
            $table->decimal('quantite', 8, 2);
            $table->decimal('prix_unitaire', 8, 2);
            $table->decimal('taux_tva', 5, 2);
            $table->decimal('montant_ht', 8, 2);
            $table->decimal('montant_ttc', 8, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('decompte_items');
    }
};
