<?php

namespace Database\Seeders\Permissions;

use App\Models\PermissionGroup;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BonLivraisonPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Use ASCII-safe name to avoid encoding issues
        $groupName = 'Gestion des Bons de Livraison';
        
        $permissionGroup = PermissionGroup::firstOrCreate([
            'name' => $groupName,
        ]);

        // Explicit permissions covering all routes
        $permissions = [
            ['name' => 'list_bonLivraisons',   'display_name' => 'Voir la liste des bons de livraison'],
            ['name' => 'show_bonLivraisons',    'display_name' => 'Voir le détail d\'un bon de livraison'],
            ['name' => 'validate_bonLivraisons', 'display_name' => 'Valider un bon de livraison'],
            ['name' => 'pdf_bonLivraisons',     'display_name' => 'Générer PDF d\'un bon de livraison'],
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