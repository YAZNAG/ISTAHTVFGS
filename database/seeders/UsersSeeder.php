<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Populates the users table with initial data.
     */
    public function run()
    {
        DB::table('users')->insert([
            [
                'id' => 1,
                'name' => 'Test User',
                'email' => 'test@example.com',
                'email_verified_at' => '2025-09-27 18:57:20',
                'password' => Hash::make('123456789'),
                'status' => 1,
                'remember_token' => 'SnOdK3sWsC',
                'created_at' => '2025-09-27 18:57:21',
                'updated_at' => '2025-09-27 18:57:21'
            ],
            [
                'id' => 2,
                'name' => 'YASSINE AZNAG',
                'email' => 'aznagy09@gmail.com',
                'email_verified_at' => null,
                'password' => Hash::make('123456789'),
                'status' => 1,
                'remember_token' => null,
                'created_at' => '2025-09-27 18:58:50',
                'updated_at' => '2025-09-27 18:58:50'
            ],
            [
                'id' => 3,
                'name' => 'Jean Dupont',
                'email' => 'jean.dupont@entreprise.com',
                'email_verified_at' => null,
                'password' => Hash::make('123456789'),
                'status' => 1,
                'remember_token' => null,
                'created_at' => '2025-10-05 19:17:47',
                'updated_at' => '2025-10-05 19:17:47'
            ],
            [
                'id' => 4,
                'name' => 'Marie Martin',
                'email' => 'marie.martin@entreprise.com',
                'email_verified_at' => null,
                'password' => Hash::make('123456789'),
                'status' => 1,
                'remember_token' => null,
                'created_at' => '2025-10-05 19:17:47',
                'updated_at' => '2025-10-05 19:17:47'
            ],
            [
                'id' => 5,
                'name' => 'Pierre Durand',
                'email' => 'pierre.durand@entreprise.com',
                'email_verified_at' => null,
                'password' => Hash::make('123456789'),
                'status' => 1,
                'remember_token' => null,
                'created_at' => '2025-10-05 19:17:47',
                'updated_at' => '2025-10-05 19:17:47'
            ],
            [
                'id' => 6,
                'name' => 'Sophie Leroy',
                'email' => 'sophie.leroy@entreprise.com',
                'email_verified_at' => null,
                'password' => Hash::make('123456789'),
                'status' => 1,
                'remember_token' => null,
                'created_at' => '2025-10-05 19:17:47',
                'updated_at' => '2025-10-05 19:17:47'
            ]
        ]);

    }
}