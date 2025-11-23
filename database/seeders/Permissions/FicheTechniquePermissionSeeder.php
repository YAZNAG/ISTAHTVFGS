<?php

namespace Database\Seeders\Permissions;

use App\Models\PermissionGroup;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FicheTechniquePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissionGroup = PermissionGroup::firstOrCreate([
            'name' => 'Gestion des Fiches Techniques',
        ]);

        $permissions = [
            ['name' => 'list_ficheTechniques',      'display_name' => 'Voir la liste des fiches techniques'],
            ['name' => 'show_ficheTechniques',       'display_name' => 'Voir le détail d\'une fiche technique'],
            ['name' => 'create_ficheTechniques',     'display_name' => 'Créer des fiches techniques'],
            ['name' => 'edit_ficheTechniques',       'display_name' => 'Modifier des fiches techniques'],
            ['name' => 'delete_ficheTechniques',     'display_name' => 'Supprimer des fiches techniques'],
            ['name' => 'pdf_ficheTechniques',        'display_name' => 'Générer PDF d\'une fiche technique'],
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
