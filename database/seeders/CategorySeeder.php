<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create sample categories
        $categories = [
            [
                'name' => 'Pain Relief',
                'description' => 'Medications for pain management and relief',
            ],
            [
                'name' => 'Cold & Flu',
                'description' => 'Products for treating cold and flu symptoms',
            ],
            [
                'name' => 'Vitamins & Supplements',
                'description' => 'Nutritional supplements and vitamins',
            ],
            [
                'name' => 'First Aid',
                'description' => 'First aid supplies and wound care products',
            ],
            [
                'name' => 'Skincare',
                'description' => 'Products for skin health and treatment',
            ],
        ];
        
        foreach ($categories as $category) {
            Category::create($category);
        }
    }
} 