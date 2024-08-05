<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
        $user = User::create([
            'name' => 'SuperAdmin',
            'phone' => '20123123',
            'email' => 'superadmin@gmail.com',
            'password' => Hash::make('superadmin'),
            'status' =>'ACTIVE',

        ]);

        $user->assignRole('superadmin');

        $user = User::create([
            'name' => 'admin',
            'phone' => '20123123',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('adminadmin'),
            'status' =>'ACTIVE',
        ]);

        $user->assignRole('admin');

        $user = User::create([
            'name' => 'instagrameur',
            'phone' => '20123123',
            'email' => 'instagrameur@gmail.com',
            'password' => Hash::make('instagrameur'),
            'status' =>'ACTIVE',
            

        ]);

        $user->assignRole('provider-intern');

        $user = User::create([
            'name' => 'fournisseur',
            'phone' => '20123123',
            'email' => 'fournisseur@gmail.com',
            'password' => Hash::make('fournisseur'),
            'status' =>'ACTIVE',
           

        ]);

        $user->assignRole('provider-extern');
        
    }
}
