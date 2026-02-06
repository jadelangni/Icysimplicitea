<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This table stores the Bill of Materials (BOM) / Recipe for each product.
     * Links products to their required ingredients with quantities.
     */
    public function up(): void
    {
        Schema::create('product_ingredients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('ingredient_id')->constrained()->onDelete('cascade');
            $table->decimal('quantity_required', 10, 2); // Amount of ingredient needed per product unit
            $table->string('unit')->nullable(); // Unit for the quantity (grams, ml, pieces, etc.)
            $table->timestamps();
            
            // Unique constraint to prevent duplicate product-ingredient combinations
            $table->unique(['product_id', 'ingredient_id']);
            
            // Indexes for faster lookups
            $table->index('product_id');
            $table->index('ingredient_id');
        });

        // Add product_type to products table to distinguish direct products from composite ones
        Schema::table('products', function (Blueprint $table) {
            $table->enum('product_type', ['direct', 'composite'])->default('composite')->after('is_active');
            // direct = finished goods (cookies, bottled drinks) - deduct from product inventory
            // composite = made-to-order items (milk tea) - deduct from ingredients
        });

        // Add branch_id to ingredients for branch-specific ingredient tracking
        Schema::table('ingredients', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('id')->constrained()->onDelete('cascade');
            $table->index('branch_id');
        });

        // Add inventory_synced flag to sales to track if inventory was deducted
        Schema::table('sales', function (Blueprint $table) {
            $table->boolean('inventory_synced')->default(false)->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn('inventory_synced');
        });

        Schema::table('ingredients', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn('branch_id');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('product_type');
        });

        Schema::dropIfExists('product_ingredients');
    }
};
