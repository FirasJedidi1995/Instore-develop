<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SizeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sizes = [
            'XS',
            'S',
            'M',
            'L',
            'XL',
            'XXL',
            'XXXL',
            // Ajoutez d'autres couleurs si nécessaire
        ];

        foreach ($sizes as $size) {
            DB::table('sizes')->insert([
                'name' => $size,
            ]);
        }
    }
}
