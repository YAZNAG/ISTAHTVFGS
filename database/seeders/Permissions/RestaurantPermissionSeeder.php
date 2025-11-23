<?php

namespace Database\Seeders\Permissions;

use App\Models\PermissionGroup;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RestaurantPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissionGroup = PermissionGroup::firstOrCreate([
            'name' => 'Gestion des Restaurants',
        ]);

        $permissions = [
            // Articles
            ['name' => 'list_restaurants',      'display_name' => 'Voir la liste des restaurants'],
            ['name' => 'show_restaurants',       'display_name' => 'Voir le détail d\'un restaurant'],
            ['name' => 'create_restaurants',     'display_name' => 'Créer des restaurants'],
            ['name' => 'edit_restaurants',       'display_name' => 'Modifier des restaurants'],
            ['name' => 'delete_restaurants',     'display_name' => 'Supprimer des restaurants'],
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
