<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menu_collectivites', function (Blueprint $table) {
            $table->integer('effectif_petit_dejeuner')->nullable()->after('effectif');
            $table->integer('effectif_dejeuner')->nullable()->after('effectif_petit_dejeuner');
            $table->integer('effectif_diner')->nullable()->after('effectif_dejeuner');
        });

        // Valeur par defaut pour les menus existants : on reporte l'effectif unique sur le dejeuner.
        DB::table('menu_collectivites')->update([
            'effectif_dejeuner' => DB::raw('effectif'),
        ]);
    }

    public function down(): void
    {
        Schema::table('menu_collectivites', function (Blueprint $table) {
            $table->dropColumn(['effectif_petit_dejeuner', 'effectif_dejeuner', 'effectif_diner']);
        });
    }
};
