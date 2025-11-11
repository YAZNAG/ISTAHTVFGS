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
        Schema::create('ligne_sortie_stocks', function (Blueprint $table) {
            $table->id();
            $table->decimal('quantite', 10, 2);
            $table->decimal('prix_unitaire', 10, 2);
            $table->decimal('taux_tva', 5, 2);
            $table->foreignId('sortie_stock_id')->constrained('sortie_stocks');
            $table->foreignId('article_id')->constrained('articles');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ligne_sortie_stocks');
    }
};
