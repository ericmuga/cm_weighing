<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Item;

class ItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $items = array (
            ['code' => 'BG1051', 'description' => 'Red Offals = Cow', 'category' => 'cm-offals'],
            ['code' => 'BG1054', 'description' => 'Hide/Skin, Cow', 'category' => 'cm-offals'],
            ['code' => 'BG1056', 'description' => 'Blood, Cow', 'category' => 'cm-offals'],
            ['code' => 'BG1057', 'description' => 'Tripe fat', 'category' => 'cm-offals'],
            ['code' => 'BG1058', 'description' => 'Carcass Tripe Trimmings', 'category' => 'cm-offals'],
            ['code' => 'BG1059', 'description' => 'Ox Head (per piece)', 'category' => 'cm-offals'],
            ['code' => 'BG1060', 'description' => 'Hooves (4 pieces), Cow', 'category' => 'cm-offals'],
            ['code' => 'BG1062', 'description' => 'Green Offals (ONLY)', 'category' => 'cm-offals'],
            ['code' => 'BG1063', 'description' => 'Ox Heart', 'category' => 'cm-offals'],
            ['code' => 'BG1064', 'description' => 'Head Musks (Skins)', 'category' => 'cm-offals'],
            ['code' => 'BG1065', 'description' => 'Cow Bile Juice', 'category' => 'cm-offals'],
            ['code' => 'BG1066', 'description' => 'Beef Omasum', 'category' => 'cm-offals'],
            ['code' => 'BG1068', 'description' => 'Beef Cardio =(Tripe Trimmings)', 'category' => 'cm-offals'],
            ['code' => 'BG1254', 'description' => 'Beef Bull Testicles', 'category' => 'cm-offals'],
            ['code' => 'BG1321', 'description' => 'Blood Plasma', 'category' => 'cm-offals'],
            ['code' => 'BG1482', 'description' => 'Ox=Liver', 'category' => 'cm-offals'],
            ['code' => 'BJ31100265', 'description' => 'Ox Horn (per unit)', 'category' => 'cm-offals'],
            ['code' => 'BJ31100316', 'description' => 'Beef Omasum Trimmings /Kg', 'category' => 'cm-offals'],
        );        

        foreach ($items as $item) {
            Item::create($item);
        }
    }
}
