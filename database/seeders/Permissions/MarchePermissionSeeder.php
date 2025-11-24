<?php

namespace Database\Seeders\Permissions;

use App\Models\PermissionGroup;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MarchePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Use ASCII-safe name to avoid encoding issues
        $groupName = 'Gestion des Marches';
        
        $permissionGroup = PermissionGroup::firstOrCreate([
            'name' => $groupName,
        ]);

        // Explicit permissions covering all routes
        $permissions = [
            ['name' => 'list_marches',    'display_name' => 'Voir la liste des marches'],
            ['name' => 'show_marches',     'display_name' => 'Voir le détail d\'un marche'],
            ['name' => 'create_marches',   'display_name' => 'Créer des marches'],
            ['name' => 'validate_marches', 'display_name' => 'Valider un marche'],
            ['name' => 'export_marches',   'display_name' => 'Exporter des marches'],
            ['name' => 'pdf_marches',      'display_name' => 'Générer PDF d\'un marche'],
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