<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create admin user if doesn't exist
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'language' => 'en',
            ]
        );
        
        // Create worker user if doesn't exist
        User::firstOrCreate(
            ['email' => 'worker@example.com'],
            [
                'name' => 'Worker User',
                'password' => Hash::make('password'),
                'role' => 'worker',
                'language' => 'en',
            ]
        );
    }
} 