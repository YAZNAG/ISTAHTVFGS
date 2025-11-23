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
            ['name' => 'bon-commandes.index',    'display_name' => 'Voir la liste des bons de commande'],
            ['name' => 'bon-commandes.show',     'display_name' => 'Voir le détail d\'un bon de commande'],
            ['name' => 'bon-commandes.create',   'display_name' => 'Créer des bons de commande'],
            ['name' => 'bon-commandes.edit',     'display_name' => 'Modifier des bons de commande'],
            ['name' => 'bon-commandes.delete',   'display_name' => 'Supprimer des bons de commande'],
            ['name' => 'bon-commandes.export',   'display_name' => 'Exporter les bons de commande'],
            ['name' => 'bon-commandes.pdf',      'display_name' => 'Générer PDF d\'un bon de commande'],
            ['name' => 'bon-commandes.validate', 'display_name' => 'Valider un bon de commande'],
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