<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use function Laravel\Prompts\table;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('restaurants', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->unsignedInteger('effectif');
            $table->string('responsable');
            $table->string('motif')->nullable();
            $table->foreignId('created_by')->constrained('users', 'id');
            $table->timestamps();
        });

        Schema::create('restaurant_items', function (Blueprint $table) {
            $table->id();
            $table->decimal('prix_unitaire', 8, 2);
            $table->decimal('quantite', 8, 2);
            $table->decimal('taux_tva', 5, 2);
            $table->foreignId('restaurant_id')->constrained('restaurants');
            $table->foreignId('article_id')->constrained('articles');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('restaurant_items');
        Schema::dropIfExists('restaurants');

    }
};
