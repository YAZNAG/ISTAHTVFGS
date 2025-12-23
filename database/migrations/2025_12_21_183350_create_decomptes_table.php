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
        Schema::create('decomptes', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->foreignId('marche_id')->constrained('bon_commandes', 'id');
            $table->boolean('final');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('decomptes');
    }
};
