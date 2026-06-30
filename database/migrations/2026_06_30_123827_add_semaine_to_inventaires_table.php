<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventaires', function (Blueprint $table) {
            $table->string('semaine', 8)->nullable()->unique()->after('mois');
        });

        // Migration des inventaires existants : on prend la 1ere semaine ISO du mois comme valeur par defaut.
        DB::table('inventaires')->whereNotNull('mois')->orderBy('id')->get()->each(function ($inventaire) {
            $date = \Carbon\Carbon::createFromFormat('Y-m', $inventaire->mois)->startOfMonth();
            $semaine = $date->format('o-\WW');

            DB::table('inventaires')->where('id', $inventaire->id)->update(['semaine' => $semaine]);
        });
    }

    public function down(): void
    {
        Schema::table('inventaires', function (Blueprint $table) {
            $table->dropColumn('semaine');
        });
    }
};
