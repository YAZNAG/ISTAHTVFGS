<?php

namespace Database\Seeders\Permissions;

use App\Models\PermissionGroup;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DemandePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissionGroup = PermissionGroup::firstOrCreate([
            'name' => 'Gestion des Demandes',
        ]);

        $permissions = [
            ['name' => 'list_demandes',     'display_name' => 'Voir la liste des demandes'],
            ['name' => 'show_demandes',      'display_name' => 'Voir le détail d\'une demande'],
            ['name' => 'create_demandes',    'display_name' => 'Créer une demande'],
            ['name' => 'validate_demandes',  'display_name' => 'Valider une demande'],
        ];

        foreach ($permissions as $permission) {
            $permissionGroup->permissions()->firstOrCreate(
                ['name' => $permission['name']],
                ['display_name' => $permission['display_name']]
            );
        }
    }
}