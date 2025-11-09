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
        Schema::create('mouvement_stocks', function (Blueprint $table) {
            $table->id();
            $table->string('type'); # IN - OUT
            $table->string('type_mouvement'); # ['reception', 'retour', 'usage_interne']
            
            // Date et heure
            $table->dateTime('date_mouvement');
            
            // Article
            $table->decimal('prix_unitaire', 10, 2);
            $table->decimal('taux_tva', 10, 2);
            
            // Quantités
            $table->decimal('quantite_entree', 10, 2)->default(0);
            $table->decimal('quantite_sortie')->default(0);
            $table->decimal('quantite_actuelle')->default(0);
            
            // Références
            $table->text('motif')->nullable();
            
            $table->nullableMorphs('referenceable'); // SorieStock or EntreeStock
            $table->foreignId('article_id')->constrained('articles');
            $table->foreignId('created_by')->nullable()->constrained('users', 'id');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mouvement_stocks');
    }
};
