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
         Schema::create('demandes', function (Blueprint $table) {
            $table->id();
            $table->string('numero')->unique(); // e.g. DEM-2025-0001
            $table->foreignId('demandeur_id')->constrained('users');
            $table->foreignId('fiche_id')->constrained('fiches_techniques');
            $table->text('motif')->nullable(); // reason or comment for the demand
            $table->string('statut');
            $table->timestamp('date_demande')->useCurrent();
            $table->timestamp('date_validation')->nullable();
            $table->foreignId('valide_par')->nullable()->constrained('users')->nullOnDelete();
            $table->text('commentaire_validation')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('demandes');
    }
};
