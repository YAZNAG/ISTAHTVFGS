<?php

namespace Database\Seeders\Permissions;

use App\Models\PermissionGroup;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissionGroup = PermissionGroup::firstOrCreate([
            'name' => 'Gestion des Rôles',
        ]);

        $permissions = [
            ['name' => 'list_roles',   'display_name' => 'Voir la liste des rôles'],
            ['name' => 'create_roles',  'display_name' => 'Créer des rôles'],
            ['name' => 'edit_roles',    'display_name' => 'Modifier des rôles'],
            ['name' => 'delete_roles',  'display_name' => 'Supprimer des rôles'],
        ];

        foreach ($permissions as $permission) {
            $permissionGroup->permissions()->firstOrCreate(
                ['name' => $permission['name']],
                ['display_name' => $permission['display_name']]
            );
        }
    }
}