<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ColorsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $colors = [
            'Red',
            'Blue',
            'Green',
            'Yellow',
            'Purple',
            // Ajoutez d'autres couleurs si nécessaire
        ];

        foreach ($colors as $color) {
            DB::table('colors')->insert([
                'name' => $color,
            ]);
        }
    
    }
}
