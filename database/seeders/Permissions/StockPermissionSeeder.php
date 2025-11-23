<?php

namespace Database\Seeders\Permissions;

use App\Models\PermissionGroup;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StockPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissionGroup = PermissionGroup::firstOrCreate([
            'name' => 'Gestion des Stocks',
        ]);

        $permissions = [
            ['name' => 'entree_stocks',          'display_name' => 'Voir la liste des entrées'],
            ['name' => 'sortie_stocks',          'display_name' => 'Voir la liste des sorties'],
            ['name' => 'articles_stocks',         'display_name' => 'Voir la liste des articles en stock'],
        ];

        foreach ($permissions as $permission) {
            $permissionGroup->permissions()->firstOrCreate(
                ['name' => $permission['name']],
                ['display_name' => $permission['display_name']]
            );
        }
    }
}