<?php

namespace Database\Seeders\Permissions;

use App\Models\PermissionGroup;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BonReceptionPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $groupName = 'Gestion des Bons de Reception';
        
        $permissionGroup = PermissionGroup::firstOrCreate([
            'name' => $groupName,
        ]);

        // Explicit permissions covering all routes
        $permissions = [
            ['name' => 'list_bonReceptions',   'display_name' => 'Voir la liste des bons de reception'],
            ['name' => 'show_bonReceptions',    'display_name' => 'Voir le detail d\'un bon de reception'],
            ['name' => 'create_bonReceptions',  'display_name' => 'Creer des bons de reception'],
            ['name' => 'destroy_bonReceptions', 'display_name' => 'Supprimer des bons de reception'],
            ['name' => 'pdf_bonReceptions',     'display_name' => 'Generer PDF d\'un bon de reception'],
        ];

        // Create permissions
        foreach ($permissions as $permission) {
            $permissionGroup->permissions()->firstOrCreate(
                ['name' => $permission['name']],
                ['display_name' => $permission['display_name']]
            );
        }
    }
}