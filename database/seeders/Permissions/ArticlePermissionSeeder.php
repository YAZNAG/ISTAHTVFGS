<?php

namespace Database\Seeders\Permissions;

use App\Models\PermissionGroup;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ArticlePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissionGroup = PermissionGroup::firstOrCreate([
            'name' => 'Gestion des Articles & Catégories',
        ]);

        $permissions = [
            // Articles
            ['name' => 'list_articles',      'display_name' => 'Voir la liste des articles'],
            ['name' => 'show_articles',       'display_name' => 'Voir le détail d\'un article'],
            ['name' => 'create_articles',     'display_name' => 'Créer des articles'],
            ['name' => 'edit_articles',       'display_name' => 'Modifier des articles'],
            // ['name' => 'delete_articles',     'display_name' => 'Supprimer des articles'],
            
            // Categories
            ['name' => 'list_categories',      'display_name' => 'Voir la liste des catégories'],
            ['name' => 'create_categories',   'display_name' => 'Créer des catégories'],
            ['name' => 'edit_categories',     'display_name' => 'Modifier des catégories'],
            ['name' => 'delete_categories',   'display_name' => 'Supprimer des catégories'],
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
