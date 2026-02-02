<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Product;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // This migration updates existing products with old option formats
        // to ensure compatibility with the new additive pricing system
        
        $products = Product::whereNotNull('options')->get();
        
        foreach ($products as $product) {
            $options = $product->options;
            $updated = false;
            
            if (is_array($options)) {
                foreach ($options as &$option) {
                    if (isset($option['values']) && is_array($option['values'])) {
                        foreach ($option['values'] as &$value) {
                            // Convert old "Label:Price" string format to object format
                            if (is_string($value) && strpos($value, ':') !== false) {
                                $parts = explode(':', $value, 2);
                                if (count($parts) === 2 && is_numeric(trim($parts[1]))) {
                                    $value = [
                                        'label' => trim($parts[0]),
                                        'price' => (float) trim($parts[1])
                                    ];
                                    $updated = true;
                                }
                            }
                        }
                    }
                }
            }
            
            if ($updated) {
                $product->options = $options;
                $product->save();
                echo "Updated product: {$product->name} (ID: {$product->id})\n";
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // This migration is not reversible as it's a data format update
    }
};