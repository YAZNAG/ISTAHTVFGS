<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();

        Schema::create('bon_commandes', function (Blueprint $table) use ($driver) {
            $table->id();
            $table->string('reference')->unique();
            $table->string('objet');
            $table->text('description')->nullable();
            $table->date('date_debut');
            $table->date('date_fin');

            $dateMiseLigne = $table->date('date_mise_ligne');
            $dateLimiteReception = $table->date('date_limite_reception');

            if ($driver !== 'sqlite') {
                $dateMiseLigne->default(DB::raw('CURDATE()'));
                $dateLimiteReception->default(DB::raw('DATE_ADD(CURDATE(), INTERVAL 15 DAY)'));
            }

            $table->string('statut')->nullable();
            $table->longText('pieces_jointes')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('categorie_id')->constrained('marche_categories');
            $table->foreignId('categorie_principale_id')->constrained('categorie_principales');
            $table->foreignId('nature_prestation_id')->constrained('nature_prestations');
            $table->foreignId('fournisseur_id')->nullable()->constrained('fournisseurs');
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users'); 
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bon_commandes');
    }
};
