<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Models\Category;

class FixProductOptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:product-options';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix product options with proper pricing structure';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Fixing product options...');
        
        // Find products that might need fixing
        $products = Product::whereNotNull('options')->get();
        
        foreach ($products as $product) {
            $this->line("Checking product: {$product->name}");
            $this->line("Current options: " . json_encode($product->options));
            
            // Update to fixed pricing: 16oz = ₱65, 22oz = ₱80
            $product->options = [
                [
                    'name' => 'Size',
                    'values' => [
                        ['label' => '16oz', 'price' => 65],
                        ['label' => '22oz', 'price' => 80],
                    ]
                ]
            ];
            $product->save();
            
            $this->info("✓ Updated {$product->name} with fixed pricing");
            $this->line("New options: " . json_encode($product->options));
            continue;
            
            $updated = false;
            $options = $product->options;
            
            if (is_array($options)) {
                // First, merge duplicate option names
                $mergedOptions = [];
                foreach ($options as $option) {
                    $name = $option['name'] ?? '';
                    if (!$name) continue;
                    
                    if (!isset($mergedOptions[$name])) {
                        $mergedOptions[$name] = ['name' => $name, 'values' => []];
                    }
                    
                    $values = $option['values'] ?? [];
                    if (!is_array($values)) {
                        $values = [$values];
                    }
                    
                    foreach ($values as $value) {
                        if (is_string($value)) {
                            // Convert string to proper format with pricing
                            if (strtolower($name) === 'size') {
                                if (strtolower($value) === '16oz') {
                                    $mergedOptions[$name]['values'][] = ['label' => $value, 'price' => 0];
                                } elseif (strtolower($value) === '22oz') {
                                    $mergedOptions[$name]['values'][] = ['label' => $value, 'price' => 10];
                                } else {
                                    $mergedOptions[$name]['values'][] = ['label' => $value, 'price' => 0];
                                }
                                $updated = true;
                            } else {
                                $mergedOptions[$name]['values'][] = ['label' => $value, 'price' => 0];
                                $updated = true;
                            }
                        } else {
                            // Already in correct format
                            $mergedOptions[$name]['values'][] = $value;
                        }
                    }
                }
                
                // Convert back to array
                if ($updated) {
                    $options = array_values($mergedOptions);
                }
            }
            
            if ($updated) {
                $product->options = $options;
                $product->save();
                $this->info("✓ Updated {$product->name}");
                $this->line("New options: " . json_encode($product->options));
            } else {
                $this->line("✓ {$product->name} already has correct format");
            }
        }
        
        // Create a test product if none exist
        if ($products->isEmpty()) {
            $category = Category::first();
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
                                ['label' => '16oz', 'price' => 0],
                                ['label' => '22oz', 'price' => 10],
                            ]
                        ]
                    ]
                ]);
                
                $this->info("Created test product: {$product->name}");
            }
        }
        
        $this->info('Product options fixed!');
    }
}
