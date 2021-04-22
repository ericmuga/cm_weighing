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
                    'code' => 'BG1021',
                    'description' => 'Steer - High Grade Carcass',
                ],
                [
                    'code' => 'BG1022',
                    'description' => 'Heifer - High Grade Carcass',
                ],
                [
                    'code' => 'BG1023',
                    'description' => 'Bull - High Grade Carcass',
                ],
                [
                    'code' => 'BG1024',
                    'description' => 'Cow - High Grade Carcass',
                ],
                [
                    'code' => 'BG1031',
                    'description' => 'Steer - Comm. Grade Carcass',
                ],
                [
                    'code' => 'BG1032',
                    'description' => 'Heifer - Comm. Grade Carcass',
                ],
                [
                    'code' => 'BG1033',
                    'description' => 'Bull - Comm. Grade Carcass',
                ],
                [
                    'code' => 'BG1034',
                    'description' => 'Cow - Comm. Grade Carcass',
                ],
                [
                    'code' => 'BG1036',
                    'description' => 'CMFAQ Grade Carcass',
                ],
                [
                    'code' => 'BG1037',
                    'description' => 'CMSTD Grade Carcass',
                ],
                [
                    'code' => 'BG1202',
                    'description' => 'Goat Carcass',
                ],
                [
                    'code' => 'BG1900',
                    'description' => 'Lamb, Carcass',
                ],

            ];

        DB::table('carcass_types')->insert($types);
    }
}
