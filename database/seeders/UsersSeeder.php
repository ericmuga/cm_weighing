<?php

namespace Database\Seeders;

use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users =
            [
                // slaughter user
                [
                    'username' => 'EKaranja',
                    'email' => Str::random(10) . '@gmail.com',
                    'password' => Hash::make('1234')
                ],
            ];

        DB::table('users')->insert($users);
    }
}
