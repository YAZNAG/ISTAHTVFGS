<?php

namespace Database\Seeders\Permissions;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class ApplicationRolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            'manager',
            'magasinier',
            'formateur',
        ];

        foreach ($roles as $roleName) {
            Role::create([
                'name' => $roleName,
                'guard_name' => 'web'
            ]);
        }
    }
}
