<?php

use App\Models\Product;

// Find the Classic Milk Tea product and update it with proper size pricing
$product = Product::where('name', 'like', '%Classic Milk Tea%')->first();

if ($product) {
    echo "Found product: {$product->name}\n";
    echo "Current options: " . json_encode($product->options) . "\n";
    
    // Set proper size options with pricing
    $product->options = [
        [
            'name' => 'Size',
            'values' => [
                ['label' => '16oz', 'price' => 0],     // Base price (no modifier)
                ['label' => '22oz', 'price' => 10],    // +₱10 for larger size
            ]
        ]
    ];
    
    $product->save();
    
    echo "Updated product options: " . json_encode($product->options) . "\n";
    echo "Product updated successfully!\n";
} else {
    echo "Classic Milk Tea product not found. Creating sample product...\n";
    
    // Create a test product if it doesn't exist
    $category = \App\Models\Category::first();
    if ($category) {
        $product = Product::create([
            'name' => 'Classic Milk Tea',
            'description' => 'Traditional milk tea with classic flavor',
            'price' => 65.00,
            'category_id' => $category->id,
            'is_active' => true,
            'options' => [
                [
                    'name' => 'Size',
                    'values' => [
                        ['label' => '16oz', 'price' => 0],     // Base price
                        ['label' => '22oz', 'price' => 10],    // +₱10
                    ]
                ]
            ]
        ]);
        
        echo "Created test product: {$product->name}\n";
        echo "Options: " . json_encode($product->options) . "\n";
    }
}