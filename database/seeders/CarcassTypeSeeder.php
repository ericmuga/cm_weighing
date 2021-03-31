<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CarcassTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $types =
            [
                [
                    'code' => 'BG1013',
                    'description' => 'Comm Grade - Bull Livestock',
                ],
                [
                    'code' => 'BG1006',
                    'description' => 'High Grade-Steer, Livestock',
                ]

            ];

        DB::table('carcass_types')->insert($types);
    }
}
