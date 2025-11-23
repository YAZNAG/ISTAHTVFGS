<?php

namespace Database\Seeders\Permissions;

use App\Models\PermissionGroup;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissionGroup = PermissionGroup::firstOrCreate([
            'name' => 'Gestion des Utilisateurs',
        ]);

        $permissions = [
            ['name' => 'list_utilisateurs',   'display_name' => 'Voir la liste des utilisateurs'],
            ['name' => 'show_utilisateurs',    'display_name' => 'Voir le détail d\'un utilisateur'],
            ['name' => 'create_utilisateurs',  'display_name' => 'Créer des utilisateurs'],
            ['name' => 'edit_utilisateurs',    'display_name' => 'Modifier des utilisateurs'],
        ];

        foreach ($permissions as $permission) {
            $permissionGroup->permissions()->firstOrCreate(
                ['name' => $permission['name']],
                ['display_name' => $permission['display_name']]
            );
        }
    }
}
