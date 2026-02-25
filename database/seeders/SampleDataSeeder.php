<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Client;
use App\Models\Sale;
use App\Models\User;
use App\Models\SaleItem;
use Carbon\Carbon;

class SampleDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create sample products
        $products = [
            [
                'name' => 'Vitamin C',
                'quantity' => 25,
                'expiry_date' => '2025-08-10',
                'supplier' => 'Pharmaco SARL',
                'price' => 50.00,
                'category_id' => 1,
                'barcode' => '9000000000001',
                'batch_number' => 'VC-2023-001',
                'manufacturing_date' => '2023-08-10',
                'reorder_point' => 10,
                'optimal_stock_level' => 30,
                'dci' => 'Ascorbic Acid',
                'dosage_form' => 'Tablet',
                'therapeutic_class' => 'Vitamin',
                'composition' => '500mg Ascorbic Acid',
                'indications' => 'Vitamin C deficiency, immune support',
                'contraindications' => 'Kidney stones, iron overload',
                'side_effects' => 'Nausea, heartburn, headache at high doses',
                'storage_conditions' => 'Store in a cool, dry place'
            ],
            [
                'name' => 'Paracetamol',
                'quantity' => 50,
                'expiry_date' => '2024-12-15',
                'supplier' => 'MediSupply Inc.',
                'price' => 25.00,
                'category_id' => 2,
                'barcode' => '9000000000002',
                'batch_number' => 'PC-2023-002',
                'manufacturing_date' => '2023-01-15',
                'reorder_point' => 15,
                'optimal_stock_level' => 60,
                'dci' => 'Acetaminophen',
                'dosage_form' => 'Tablet',
                'therapeutic_class' => 'Analgesic',
                'composition' => '500mg Acetaminophen',
                'indications' => 'Pain relief, fever reduction',
                'contraindications' => 'Liver disease, alcohol consumption',
                'side_effects' => 'Liver damage at high doses',
                'storage_conditions' => 'Store below 25°C'
            ],
            [
                'name' => 'Ibuprofen',
                'quantity' => 30,
                'expiry_date' => '2024-10-20',
                'supplier' => 'MediSupply Inc.',
                'price' => 35.00,
                'category_id' => 2,
                'barcode' => '9000000000003',
                'batch_number' => 'IB-2023-003',
                'manufacturing_date' => '2023-02-20',
                'reorder_point' => 10,
                'optimal_stock_level' => 40,
                'dci' => 'Ibuprofen',
                'dosage_form' => 'Tablet',
                'therapeutic_class' => 'NSAID',
                'composition' => '400mg Ibuprofen',
                'indications' => 'Pain relief, inflammation reduction, fever',
                'contraindications' => 'Peptic ulcer, asthma, heart failure',
                'side_effects' => 'Stomach upset, heartburn, dizziness',
                'storage_conditions' => 'Store below 25°C, protect from light'
            ],
            [
                'name' => 'Aspirin',
                'quantity' => 40,
                'expiry_date' => '2025-05-01',
                'supplier' => 'Pharmaco SARL',
                'price' => 20.00,
                'category_id' => 2,
                'barcode' => '9000000000004',
                'batch_number' => 'AS-2023-004',
                'manufacturing_date' => '2023-05-01',
                'reorder_point' => 15,
                'optimal_stock_level' => 50,
                'dci' => 'Acetylsalicylic Acid',
                'dosage_form' => 'Tablet',
                'therapeutic_class' => 'NSAID',
                'composition' => '100mg Acetylsalicylic Acid',
                'indications' => 'Pain relief, fever, blood thinning',
                'contraindications' => 'Bleeding disorders, children under 16',
                'side_effects' => 'Stomach irritation, increased bleeding risk',
                'storage_conditions' => 'Store in a cool, dry place'
            ],
            [
                'name' => 'Vitamin D',
                'quantity' => 15,
                'expiry_date' => '2023-11-30',
                'supplier' => 'NutriHealth Ltd.',
                'price' => 60.00,
                'category_id' => 1,
                'barcode' => '9000000000005',
                'batch_number' => 'VD-2023-005',
                'manufacturing_date' => '2022-11-30',
                'reorder_point' => 5,
                'optimal_stock_level' => 20,
                'dci' => 'Cholecalciferol',
                'dosage_form' => 'Capsule',
                'therapeutic_class' => 'Vitamin',
                'composition' => '1000 IU Cholecalciferol',
                'indications' => 'Vitamin D deficiency, bone health',
                'contraindications' => 'Hypercalcemia, kidney stones',
                'side_effects' => 'Nausea, vomiting, weakness at high doses',
                'storage_conditions' => 'Store below 25°C, protect from light'
            ],
            [
                'name' => 'Zinc Supplements',
                'quantity' => 8,
                'expiry_date' => '2024-02-28',
                'supplier' => 'NutriHealth Ltd.',
                'price' => 45.00,
                'category_id' => 1,
                'barcode' => '9000000000006',
                'batch_number' => 'ZN-2023-006',
                'manufacturing_date' => '2022-08-28',
                'reorder_point' => 5,
                'optimal_stock_level' => 15,
                'dci' => 'Zinc Sulfate',
                'dosage_form' => 'Tablet',
                'therapeutic_class' => 'Mineral',
                'composition' => '50mg Zinc Sulfate',
                'indications' => 'Zinc deficiency, immune support',
                'contraindications' => 'Copper deficiency',
                'side_effects' => 'Nausea, stomach upset, metallic taste',
                'storage_conditions' => 'Store in a cool, dry place'
            ],
        ];

        foreach ($products as $productData) {
            Product::create($productData);
        }

        // Get the first user (admin)
        $user = User::where('role', 'admin')->first();
        
        if (!$user) {
            // If no admin user exists, use the first user available
            $user = User::first();
        }

        // Create sample sales
        $sales = [
            [
                'product_id' => 1,
                'quantity' => 2,
                'sale_date' => Carbon::now()->subDays(5),
                'client_id' => 1,
                'user_id' => $user ? $user->id : null
            ],
            [
                'product_id' => 2,
                'quantity' => 1,
                'sale_date' => Carbon::now()->subDays(3),
                'client_id' => 2,
                'user_id' => $user ? $user->id : null
            ],
            [
                'product_id' => 3,
                'quantity' => 3,
                'sale_date' => Carbon::now()->subDays(2),
                'client_id' => 3,
                'user_id' => $user ? $user->id : null
            ],
            [
                'product_id' => 1,
                'quantity' => 1,
                'sale_date' => Carbon::now()->subDays(1),
                'client_id' => 1,
                'user_id' => $user ? $user->id : null
            ],
            [
                'product_id' => 4,
                'quantity' => 2,
                'sale_date' => Carbon::now(),
                'client_id' => 2,
                'user_id' => $user ? $user->id : null
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
                'user_id' => $saleData['user_id'],
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
