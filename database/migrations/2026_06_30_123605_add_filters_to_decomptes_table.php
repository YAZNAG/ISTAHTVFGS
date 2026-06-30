<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('decomptes', function (Blueprint $table) {
            $table->date('date_debut')->nullable()->after('date');
            $table->foreignId('categorie_id')->nullable()->after('marche_id')->constrained('categories')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('decomptes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('categorie_id');
            $table->dropColumn('date_debut');
        });
    }
};
