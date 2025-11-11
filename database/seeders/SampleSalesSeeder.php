<?php

namespace Database\Seeders;

use App\Models\Sale;
use App\Models\SalesItem;
use App\Models\User;
use App\Models\Product;
use Illuminate\Database\Seeder;

class SampleSalesSeeder extends Seeder
{
    public function run()
    {
        $user = User::first();
        $products = Product::take(3)->get();

        // Create some sample sales for today
        $existingCount = Sale::count();
        for ($i = 1; $i <= 3; $i++) {
            $receiptNum = $existingCount + $i;
            $sale = Sale::create([
                'receipt_number' => 'RCP-' . date('Ymd') . '-' . str_pad($receiptNum, 3, '0', STR_PAD_LEFT),
                'branch_id' => 1,
                'user_id' => $user->id,
                'subtotal' => 120.00 * $i,
                'tax_amount' => 0.00,
                'discount_amount' => 0.00,
                'total_amount' => 120.00 * $i,
                'payment_method' => $i % 2 == 0 ? 'card' : 'cash',
                'amount_paid' => 150.00 * $i,
                'change_amount' => 30.00 * $i,
            ]);

            // Add sale items
            foreach ($products as $index => $product) {
                if ($index < $i) {
                    SalesItem::create([
                        'sale_id' => $sale->id,
                        'product_id' => $product->id,
                        'quantity' => $i,
                        'unit_price' => 60.00,
                        'total_price' => 60.00 * $i,
                    ]);
                }
            }
        }

        $this->command->info('Sample sales data created successfully!');
    }
}