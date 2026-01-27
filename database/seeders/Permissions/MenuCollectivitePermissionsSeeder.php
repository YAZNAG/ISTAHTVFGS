<?php

namespace Database\Seeders\Permissions;

use App\Models\PermissionGroup;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MenuCollectivitePermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissionGroup = PermissionGroup::firstOrCreate([
            'name' => 'Gestion des Menus Collectivites',
        ]);

        $permissions = [
            ['name' => 'list_menus',   'display_name' => 'Voir la liste des menus'],
            ['name' => 'create_menus',  'display_name' => 'Créer des menus'],
            ['name' => 'edit_menus',    'display_name' => 'Modifier des menus'],
            ['name' => 'export_menus',  'display_name' => 'Exporter des menus'],
            ['name' => 'pdf_menus',  'display_name' => 'Générer PDF  menu'],
        ];

        foreach ($permissions as $permission) {
            $permissionGroup->permissions()->firstOrCreate(
                ['name' => $permission['name']],
                ['display_name' => $permission['display_name']]
            );
        }
    }
}
