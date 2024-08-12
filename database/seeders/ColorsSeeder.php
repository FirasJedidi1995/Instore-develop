<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ColorsSeeder extends Seeder
{
    public function run(): void
    {
        $colors = [
            ['name' => 'Red', 'code' => '#FF0000'],
            ['name' => 'Blue', 'code' => '#0000FF'],
            ['name' => 'Green', 'code' => null], 
            ['name' => 'Yellow', 'code' => '#FFFF00'],
            ['name' => 'Purple'], 
        ];
        

        foreach ($colors as $color) {
            DB::table('colors')->insert($color);
        }
    
    }
}