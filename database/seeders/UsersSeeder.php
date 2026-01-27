<?php

namespace Database\Seeders;

use App\Models\User;
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
        $users = [
            [
                'id'    => 2,
                'name'  => 'Stitou Mohamed',
                'email' => 'mstitou@tourisme.gov.ma',
                'email_verified_at' => null,
                'password' => Hash::make('123456789'),
                'status' => 1,
            ],
            [
                'id'    => 3,
                'name'  => 'el ghali ali',
                'email' => 'elghali.ali2015@gmail.com',
                'email_verified_at' => null,
                'password' => Hash::make('123456789'),
                'status' => 1,
            ],
            [
                'id'    => 4,
                'name'  => 'Janati Meryem',
                'email' => 'meryemjanati20@gmail.com',
                'email_verified_at' => null,
                'password' => Hash::make('123456789'),
                'status' => 1,
            ],
            [
                'id'    => 5,
                'name'  => 'Amal Mehyaoui',
                'email' => 'amalmehyaouii@gmail.com',
                'email_verified_at' => null,
                'password' => Hash::make('123456789'),
                'status' => 1,
            ],
            [
                'id'    => 6,
                'name'  => 'Chentouf Hamman Oussama',
                'email' => 'oussama.chentouf90@gmail.com',
                'email_verified_at' => null,
                'password' => Hash::make('123456789'),
                'status' => 1,
            ],
            [
                'id'    => 7,
                'name'  => 'Akel Jawaher',
                'email' => 'jawhara-1000@hotmail.com',
                'email_verified_at' => null,
                'password' => Hash::make('123456789'),
                'status' => 1,
            ],
            [
                'id'    => 8,
                'name'  => 'Addach mahjouba',
                'email' => 'addachhjiba1970@gmail.com',
                'email_verified_at' => null,
                'password' => Hash::make('123456789'),
                'status' => 1,
            ],
            [
                'id'    => 9,
                'name'  => 'Merrouni Touham',
                'email' => 'merrounitouhami@gmail.com',
                'email_verified_at' => null,
                'password' => Hash::make('123456789'),
                'status' => 1,
            ],
            [
                'id'    => 10,
                'name'  => 'test user',
                'email' => 'test@user.com',
                'email_verified_at' => null,
                'password' => Hash::make('123456789'),
                'status' => 1,
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['id' => $user['id']],   // match on primary key
                $user                     // insert / update these fields
            );
        }

    }
}