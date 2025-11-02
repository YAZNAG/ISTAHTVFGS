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
        Schema::create('bon_livraisons', function (Blueprint $table) {
            $table->id();
            $table->string('numero');
            $table->string('statut');
            $table->dateTime('date_livraison')->nullable(); 
            $table->foreignId('chef_commande_id')->constrained('chef_commandes');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('fournisseur_id')->constrained('fournisseurs');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('bon_livraisons_items', function (Blueprint $table) {
            $table->id();
            $table->decimal('quantite', 10, 2);
            $table->decimal('prix_unitaire', 10, 2);
            $table->decimal('taux_tva', 5, 2)->nullable();
            $table->decimal('montant_tva', 10, 2)->nullable();
            $table->decimal('prix_total', 10, 2);
            $table->foreignId('bon_livraison_id')->constrained('bon_livraisons');
            $table->foreignId('article_id')->constrained('articles');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bon_livraisons_items');
        Schema::dropIfExists('bon_livraisons');
    }
};
