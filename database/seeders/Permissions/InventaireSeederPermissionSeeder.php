<?php

namespace Database\Seeders\Permissions;

use App\Models\PermissionGroup;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InventaireSeederPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissionGroup = PermissionGroup::firstOrCreate([
            'name' => 'Gestion des Inventaires',
        ]);

        $permissions = [
            ['name' => 'list_inventaire',   'display_name' => 'Voir la liste des inventaires'],
            ['name' => 'fill_stock_reel',   'display_name' => 'Saisir le stock réel'],
            ['name' => 'unlock_inventaire', 'display_name' => 'Déverrouiller un inventaire'],
            ['name' => 'create_inventaire', 'display_name' => 'Créer un inventaire'],
            ['name' => 'pdf_inventaire',    'display_name' => 'Générer PDF d\'un inventaire'],
        ];


        foreach ($permissions as $permission) {
            $permissionGroup->permissions()->firstOrCreate(
                ['name' => $permission['name']],
                ['display_name' => $permission['display_name']]
            );
        }
    }
}
