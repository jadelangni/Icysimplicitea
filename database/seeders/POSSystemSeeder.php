<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class POSSystemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create branches
        $branches = [
            [
                'name' => 'Oslob Main',
                'address' => 'Main Street, Oslob, Cebu',
                'phone' => '+63 912 345 6789',
                'manager_name' => 'Maria Santos',
                'is_active' => true,
            ],
            [
                'name' => 'Santander Poblacion',
                'address' => 'Poblacion, Santander, Cebu',
                'phone' => '+63 912 345 6788',
                'manager_name' => 'Juan dela Cruz',
                'is_active' => true,
            ],
            [
                'name' => 'Looc Branch',
                'address' => 'Looc, Oslob, Cebu',
                'phone' => '+63 912 345 6787',
                'manager_name' => 'Ana Garcia',
                'is_active' => true,
            ],
        ];

        foreach ($branches as $branch) {
            \App\Models\Branch::create($branch);
        }

        // Create categories
        $categories = [
            ['name' => 'Milk Tea', 'description' => 'Various flavored milk teas', 'is_active' => true],
            ['name' => 'Fruit Tea', 'description' => 'Fresh fruit-based teas', 'is_active' => true],
            ['name' => 'Coffee', 'description' => 'Hot and cold coffee drinks', 'is_active' => true],
            ['name' => 'Snacks', 'description' => 'Light snacks and pastries', 'is_active' => true],
        ];

        foreach ($categories as $category) {
            \App\Models\Category::create($category);
        }

        // Create products
        $products = [
            // Milk Tea
            ['category_id' => 1, 'name' => 'Classic Milk Tea', 'description' => 'Traditional milk tea with tapioca pearls', 'price' => 65.00, 'is_active' => true],
            ['category_id' => 1, 'name' => 'Taro Milk Tea', 'description' => 'Creamy taro flavored milk tea', 'price' => 75.00, 'is_active' => true],
            ['category_id' => 1, 'name' => 'Matcha Milk Tea', 'description' => 'Japanese matcha milk tea', 'price' => 80.00, 'is_active' => true],
            ['category_id' => 1, 'name' => 'Chocolate Milk Tea', 'description' => 'Rich chocolate milk tea', 'price' => 70.00, 'is_active' => true],
            
            // Fruit Tea
            ['category_id' => 2, 'name' => 'Lemon Honey Tea', 'description' => 'Refreshing lemon tea with honey', 'price' => 60.00, 'is_active' => true],
            ['category_id' => 2, 'name' => 'Passion Fruit Tea', 'description' => 'Tropical passion fruit tea', 'price' => 70.00, 'is_active' => true],
            ['category_id' => 2, 'name' => 'Mango Green Tea', 'description' => 'Fresh mango with green tea', 'price' => 65.00, 'is_active' => true],
            
            // Coffee
            ['category_id' => 3, 'name' => 'Iced Coffee', 'description' => 'Cold brew coffee served with ice', 'price' => 55.00, 'is_active' => true],
            ['category_id' => 3, 'name' => 'Cappuccino', 'description' => 'Classic cappuccino with steamed milk', 'price' => 85.00, 'is_active' => true],
            
            // Snacks
            ['category_id' => 4, 'name' => 'Chicken Sandwich', 'description' => 'Grilled chicken sandwich', 'price' => 120.00, 'is_active' => true],
            ['category_id' => 4, 'name' => 'Cookies', 'description' => 'Homemade chocolate chip cookies', 'price' => 45.00, 'is_active' => true],
        ];

        foreach ($products as $product) {
            \App\Models\Product::create($product);
        }

        // Create admin user
        $admin = \App\Models\User::create([
            'name' => 'Admin User',
            'email' => 'admin@simplicitea.com',
            'password' => bcrypt('password'),
            'role' => 'owner',
            'branch_id' => 1,
            'is_active' => true,
        ]);

        // Create sample employees
        $employees = [
            [
                'name' => 'John Cashier',
                'email' => 'cashier1@simplicitea.com',
                'password' => bcrypt('password'),
                'role' => 'cashier',
                'branch_id' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Jane Supervisor',
                'email' => 'supervisor1@simplicitea.com',
                'password' => bcrypt('password'),
                'role' => 'supervisor',
                'branch_id' => 1,
                'is_active' => true,
            ],
        ];

        foreach ($employees as $employee) {
            \App\Models\User::create($employee);
        }

        // Create inventory for branch 1
        $products = \App\Models\Product::all();
        foreach ($products as $product) {
            \App\Models\Inventory::create([
                'branch_id' => 1,
                'product_id' => $product->id,
                'quantity' => rand(10, 100),
                'min_stock_level' => 5,
            ]);
        }
    }
}
