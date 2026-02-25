<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Client;
use App\Models\Sale;
use App\Models\SaleItem;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            UserSeeder::class,
            WorkerSeeder::class,
            CategorySeeder::class,
            ProductSeeder::class,
            ClientSeeder::class,
        ]);

        // Create sample products
        $products = [
            [
                'name' => 'Vitamin C',
                'quantity' => 25,
                'expiry_date' => '2025-08-10',
                'supplier' => 'Pharmaco SARL'
            ],
            [
                'name' => 'Paracetamol',
                'quantity' => 50,
                'expiry_date' => '2024-12-15',
                'supplier' => 'MediSupply Inc.'
            ],
            [
                'name' => 'Ibuprofen',
                'quantity' => 30,
                'expiry_date' => '2024-10-20',
                'supplier' => 'MediSupply Inc.'
            ],
            [
                'name' => 'Aspirin',
                'quantity' => 40,
                'expiry_date' => '2025-05-01',
                'supplier' => 'Pharmaco SARL'
            ],
            [
                'name' => 'Vitamin D',
                'quantity' => 15,
                'expiry_date' => '2023-11-30',
                'supplier' => 'NutriHealth Ltd.'
            ],
            [
                'name' => 'Zinc Supplements',
                'quantity' => 8,
                'expiry_date' => '2024-02-28',
                'supplier' => 'NutriHealth Ltd.'
            ],
        ];

        foreach ($products as $productData) {
            Product::create($productData);
        }

        // Create sample clients
        $clients = [
            [
                'name' => 'John Doe',
                'phone' => '555-1234',
                'notes' => 'Regular customer'
            ],
            [
                'name' => 'Jane Smith',
                'phone' => '555-5678',
                'notes' => 'Prefers generic medications'
            ],
            [
                'name' => 'Robert Johnson',
                'phone' => '555-9012',
                'notes' => 'Has allergies to penicillin'
            ],
        ];

        foreach ($clients as $clientData) {
            Client::create($clientData);
        }

        // Create sample sales
        $sales = [
            [
                'product_id' => 1,
                'quantity' => 2,
                'sale_date' => Carbon::now()->subDays(5),
                'client_id' => 1
            ],
            [
                'product_id' => 2,
                'quantity' => 1,
                'sale_date' => Carbon::now()->subDays(3),
                'client_id' => 2
            ],
            [
                'product_id' => 3,
                'quantity' => 3,
                'sale_date' => Carbon::now()->subDays(2),
                'client_id' => 3
            ],
            [
                'product_id' => 1,
                'quantity' => 1,
                'sale_date' => Carbon::now()->subDays(1),
                'client_id' => 1
            ],
            [
                'product_id' => 4,
                'quantity' => 2,
                'sale_date' => Carbon::now(),
                'client_id' => 2
            ],
        ];

        // Create sales with proper structure (sales table + sale_items table)
        foreach ($sales as $saleData) {
            $product = Product::find($saleData['product_id']);
            if (!$product) continue;

            // Create sale record
            $sale = Sale::create([
                'receipt_number' => 'RCP-' . str_pad(Sale::count() + 1, 6, '0', STR_PAD_LEFT),
                'sale_date' => $saleData['sale_date'],
                'client_id' => $saleData['client_id'],
                'subtotal' => $product->price * $saleData['quantity'],
                'total_amount' => $product->price * $saleData['quantity'],
                'final_amount' => $product->price * $saleData['quantity'],
            ]);

            // Create sale item record
            SaleItem::create([
                'sale_id' => $sale->id,
                'product_id' => $saleData['product_id'],
                'quantity' => $saleData['quantity'],
                'unit_price' => $product->price,
                'subtotal' => $product->price * $saleData['quantity'],
            ]);
        }
    }
}
