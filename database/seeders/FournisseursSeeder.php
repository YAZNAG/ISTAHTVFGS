<?php

namespace Database\Seeders;

use App\Models\Fournisseur;
use Database\Factories\FournisseurFactory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FournisseursSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Populates the fournisseurs table with initial data.
     */
    public function run()
    {
        // DB::table('fournisseurs')->insert([
        //     [
        //         'id' => 1,
        //         'nom' => 'YASSINE AZNAG',
        //         'contact' => 'ZELEL',
        //         'telephone' => '0688218227',
        //         'email' => 'aznagy09@gmail.com',
        //         'adresse' => 'agadir',
        //         'ville' => null,
        //         'ice' => null,
        //         'est_actif' => 1,
        //         'notes' => null,
        //         'created_at' => '2025-09-30 15:09:30',
        //         'updated_at' => '2025-09-30 15:09:30',
        //         'deleted_at' => null,
        //         'logo' => null,
        //         'raison_sociale' => 'OPTIZAWORKS'
        //     ],
        //     [
        //         'id' => 2,
        //         'nom' => 'khalil abidar',
        //         'contact' => 'ZKKEJKJDIJEF',
        //         'telephone' => '+212697569854',
        //         'email' => 'khalil@gmail.com',
        //         'adresse' => 'agadir',
        //         'ville' => 'Agadir',
        //         'ice' => '243294828449',
        //         'est_actif' => 1,
        //         'notes' => null,
        //         'created_at' => '2025-10-04 21:41:49',
        //         'updated_at' => '2025-10-04 21:41:49',
        //         'deleted_at' => null,
        //         'logo' => 'fournisseurs/logos/vhMlpRRbHZL7HHmTeTEu0G5J9YHSM5EDghnyFnoE.png',
        //         'raison_sociale' => 'AZAAZAEZEAZ'
        //     ]
        // ]);

        Fournisseur::factory()->count(2)->create();
    }
}