<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::create([
            'name' => 'admin',
            'guard_name' => 'web'
        ]);

        $user = User::create([
            'name' => 'admin user',
            'email' => 'admin@email.com',
            'password' => Hash::make('123456789'),
            'status' => 1
        ]);

        $user->assignRole('manager','admin');
    }
}
