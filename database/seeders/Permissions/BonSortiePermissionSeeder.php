<?php

namespace Database\Seeders\Permissions;

use App\Models\PermissionGroup;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BonSortiePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissionGroup = PermissionGroup::firstOrCreate([
            'name' => 'Gestion des Bons de Sortie',
        ]);

        $permissions = [
            ['name' => 'list_bonSorties',         'display_name' => 'Voir la liste des bons de sortie'],
            ['name' => 'show_bonSorties',          'display_name' => 'Voir le détail d\'un bon de sortie'],
            ['name' => 'validate_bonSorties',      'display_name' => 'Valider un bon de sortie'],
            ['name' => 'pdf_bonSorties',           'display_name' => 'Télécharger le PDF d\'un bon de sortie'],
        ];

        foreach ($permissions as $permission) {
            $permissionGroup->permissions()->firstOrCreate(
                ['name' => $permission['name']],
                ['display_name' => $permission['display_name']]
            );
        }
    }
}