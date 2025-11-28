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
        Schema::create('inventaire_lignes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventaire_id')->constrained()->cascadeOnDelete();
            $table->string('code_article');
            $table->string('unite_mesure');
            $table->string('designation');
            $table->unsignedInteger('qte_entree');
            $table->unsignedInteger('qte_sortie');
            $table->integer('stock_theorique');
            $table->integer('stock_reel')->nullable();          // saisie manuelle
            $table->integer('ecart')->nullable();               // stock_reel - stock_theorique
            $table->text('observations')->nullable();
            $table->timestamps();

            $table->unique(['inventaire_id','code_article']);
            $table->index('code_article');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventaire_lignes');
    }
};
