<?php

namespace Database\Seeders\Permissions;

use App\Models\PermissionGroup;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ChefCommandePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissionGroup = PermissionGroup::firstOrCreate([
            'name' => 'Gestion des bons de Commande',
        ]);

        $permissions = [
            ['name' => 'list_chefCommandes',       'display_name' => 'Voir la liste des bons de commande'],
            ['name' => 'show_chefCommandes',       'display_name' => 'Voir le détail d\'un bon de commande'],
            ['name' => 'create_chefCommandes',     'display_name' => 'Créer des bons de commande'],
            ['name' => 'edit_chefCommandes',       'display_name' => 'Modifier des bons de commande'],
            // ['name' => 'delete_chefCommandes',     'display_name' => 'Supprimer des bons de commande'],
            ['name' => 'validate_chefCommandes',   'display_name' => 'Valider un bon de commande'],
            ['name' => 'pdf_chefCommandes',        'display_name' => 'Générer PDF d\'un bon de commande'],
        ];

        foreach ($permissions as $permission) {
            $permissionGroup->permissions()->firstOrCreate(
                ['name' => $permission['name']],
                ['display_name' => $permission['display_name']]
            );
        }
    }
}