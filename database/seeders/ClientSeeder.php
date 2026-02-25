<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Client;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create sample clients
        $clients = [
            [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'phone' => '123-456-7890',
                'address' => '123 Main St, City',
            ],
            [
                'name' => 'Jane Smith',
                'email' => 'jane@example.com',
                'phone' => '987-654-3210',
                'address' => '456 Oak Ave, Town',
            ],
            [
                'name' => 'Robert Johnson',
                'email' => 'robert@example.com',
                'phone' => '555-123-4567',
                'address' => '789 Pine Rd, Village',
            ],
            [
                'name' => 'Sarah Williams',
                'email' => 'sarah@example.com',
                'phone' => '444-555-6666',
                'address' => '321 Elm St, County',
            ],
            [
                'name' => 'Michael Brown',
                'email' => 'michael@example.com',
                'phone' => '777-888-9999',
                'address' => '654 Maple Dr, District',
            ],
        ];
        
        foreach ($clients as $client) {
            Client::create($client);
        }
    }
} 