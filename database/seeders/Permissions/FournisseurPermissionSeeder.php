<?php

namespace Database\Seeders\Permissions;

use App\Models\PermissionGroup;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class FournisseurPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $groupName = 'Gestion des Fournisseurs';
        
        $permissionGroup = PermissionGroup::firstOrCreate([
            'name' => $groupName,
        ]);

        // Explicit permissions covering all routes
        $permissions = [
            ['name' => 'list_fournisseurs',    'display_name' => 'Voir la liste des fournisseurs'],
            ['name' => 'show_fournisseurs',     'display_name' => 'Voir le détail d\'un fournisseur'],
            ['name' => 'create_fournisseurs',   'display_name' => 'Créer des fournisseurs'],
            ['name' => 'edit_fournisseurs',     'display_name' => 'Modifier des fournisseurs'],
            ['name' => 'delete_fournisseurs',   'display_name' => 'Supprimer des fournisseurs'],
            ['name' => 'export_fournisseurs',   'display_name' => 'Exporter les fournisseurs'],
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