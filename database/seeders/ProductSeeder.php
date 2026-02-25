<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use Carbon\Carbon;

class ProductSeeder extends Seeder
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
                'name' => 'Paracetamol 500mg',
                'barcode' => '5901234123457',
                'quantity' => 100,
                'expiry_date' => Carbon::now()->addYears(2),
                'supplier' => 'PharmaCorp',
                'price' => 5.99,
                'category_id' => 1, // Pain Relief
            ],
            [
                'name' => 'Ibuprofen 200mg',
                'barcode' => '5901234123464',
                'quantity' => 75,
                'expiry_date' => Carbon::now()->addYears(1),
                'supplier' => 'MediSupply',
                'price' => 7.49,
                'category_id' => 1, // Pain Relief
            ],
            [
                'name' => 'Cold & Flu Relief',
                'barcode' => '5901234123471',
                'quantity' => 50,
                'expiry_date' => Carbon::now()->addMonths(18),
                'supplier' => 'HealthCare Inc',
                'price' => 8.99,
                'category_id' => 2, // Cold & Flu
            ],
            [
                'name' => 'Vitamin C 1000mg',
                'barcode' => '5901234123488',
                'quantity' => 120,
                'expiry_date' => Carbon::now()->addYears(3),
                'supplier' => 'VitaWell',
                'price' => 12.50,
                'category_id' => 3, // Vitamins & Supplements
            ],
            [
                'name' => 'Bandages Assorted',
                'barcode' => '5901234123495',
                'quantity' => 30,
                'expiry_date' => Carbon::now()->addYears(5),
                'supplier' => 'MediSupply',
                'price' => 4.25,
                'category_id' => 4, // First Aid
            ],
            [
                'name' => 'Moisturizing Cream',
                'barcode' => '5901234123501',
                'quantity' => 25,
                'expiry_date' => Carbon::now()->addYears(1),
                'supplier' => 'DermaCare',
                'price' => 15.75,
                'category_id' => 5, // Skincare
            ],
            [
                'name' => 'Antiseptic Solution',
                'barcode' => '5901234123518',
                'quantity' => 40,
                'expiry_date' => Carbon::now()->addMonths(24),
                'supplier' => 'HealthCare Inc',
                'price' => 6.50,
                'category_id' => 4, // First Aid
            ],
            [
                'name' => 'Multivitamin Tablets',
                'barcode' => '5901234123525',
                'quantity' => 90,
                'expiry_date' => Carbon::now()->addYears(2),
                'supplier' => 'VitaWell',
                'price' => 18.99,
                'category_id' => 3, // Vitamins & Supplements
            ],
            [
                'name' => 'Cough Syrup',
                'barcode' => '5901234123532',
                'quantity' => 15,
                'expiry_date' => Carbon::now()->addMonths(14),
                'supplier' => 'PharmaCorp',
                'price' => 9.75,
                'category_id' => 2, // Cold & Flu
            ],
            [
                'name' => 'Acne Treatment Gel',
                'barcode' => '5901234123549',
                'quantity' => 20,
                'expiry_date' => Carbon::now()->addYears(1),
                'supplier' => 'DermaCare',
                'price' => 11.25,
                'category_id' => 5, // Skincare
            ],
        ];
        
        foreach ($products as $product) {
            Product::firstOrCreate(
                ['barcode' => $product['barcode']],
                $product
            );
        }
    }
} 