<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class WorkerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create workers with different positions
        $workers = [
            [
                'name' => 'John Smith',
                'email' => 'john@example.com',
                'password' => Hash::make('password'),
                'role' => 'worker',
                'language' => 'en',
                'position' => 'Senior Pharmacist',
                'status' => 'active',
                'phone' => '+1234567890',
                'address' => '123 Main St, City',
                'permissions' => ['sales.create', 'sales.view', 'products.view'],
            ],
            [
                'name' => 'Sarah Johnson',
                'email' => 'sarah@example.com',
                'password' => Hash::make('password'),
                'role' => 'worker',
                'language' => 'en',
                'position' => 'Pharmacist',
                'status' => 'active',
                'phone' => '+1987654321',
                'address' => '456 Oak St, Town',
                'permissions' => ['sales.create', 'sales.view', 'products.view'],
            ],
            [
                'name' => 'Mohammed Ali',
                'email' => 'mohammed@example.com',
                'password' => Hash::make('password'),
                'role' => 'worker',
                'language' => 'ar',
                'position' => 'Pharmacy Technician',
                'status' => 'active',
                'phone' => '+1122334455',
                'address' => '789 Pine St, Village',
                'permissions' => ['sales.create', 'sales.view', 'products.view'],
            ],
            [
                'name' => 'Marie Dupont',
                'email' => 'marie@example.com',
                'password' => Hash::make('password'),
                'role' => 'worker',
                'language' => 'fr',
                'position' => 'Cashier',
                'status' => 'active',
                'phone' => '+1555666777',
                'address' => '321 Elm St, County',
                'permissions' => ['sales.create', 'sales.view'],
            ],
            [
                'name' => 'David Wilson',
                'email' => 'david@example.com',
                'password' => Hash::make('password'),
                'role' => 'worker',
                'language' => 'en',
                'position' => 'Inventory Manager',
                'status' => 'active',
                'phone' => '+1777888999',
                'address' => '654 Maple St, District',
                'permissions' => ['sales.view', 'products.view', 'products.edit'],
            ],
        ];

        foreach ($workers as $worker) {
            // Check if the user already exists
            if (!User::where('email', $worker['email'])->exists()) {
                User::create($worker);
                $this->command->info("Created worker: {$worker['name']}");
            } else {
                $this->command->info("Worker already exists: {$worker['name']}");
            }
        }
    }
} 