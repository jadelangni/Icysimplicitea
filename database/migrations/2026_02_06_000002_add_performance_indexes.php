<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Helper to check if an index exists on a table
     */
    private function indexExists(string $table, string $indexName): bool
    {
        $result = DB::select("SHOW INDEX FROM {$table} WHERE Key_name = ?", [$indexName]);
        return count($result) > 0;
    }

    public function up(): void
    {
        // Add indexes to sales table
        Schema::table('sales', function (Blueprint $table) {
            if (!$this->indexExists('sales', 'sales_branch_id_created_at_index')) {
                $table->index(['branch_id', 'created_at'], 'sales_branch_id_created_at_index');
            }
        });
        
        Schema::table('sales', function (Blueprint $table) {
            if (!$this->indexExists('sales', 'sales_user_id_created_at_index')) {
                $table->index(['user_id', 'created_at'], 'sales_user_id_created_at_index');
            }
        });
        
        Schema::table('sales', function (Blueprint $table) {
            if (!$this->indexExists('sales', 'sales_status_index')) {
                $table->index('status', 'sales_status_index');
            }
        });
        
        Schema::table('sales', function (Blueprint $table) {
            if (!$this->indexExists('sales', 'sales_payment_method_index')) {
                $table->index('payment_method', 'sales_payment_method_index');
            }
        });

        // Add indexes to sales_items table
        Schema::table('sales_items', function (Blueprint $table) {
            if (!$this->indexExists('sales_items', 'sales_items_product_id_index')) {
                $table->index('product_id', 'sales_items_product_id_index');
            }
        });

        // Add indexes to inventory table
        Schema::table('inventory', function (Blueprint $table) {
            if (!$this->indexExists('inventory', 'inventory_branch_product_index')) {
                $table->index(['branch_id', 'product_id'], 'inventory_branch_product_index');
            }
        });

        // Add indexes to ingredient_inventory table
        Schema::table('ingredient_inventory', function (Blueprint $table) {
            if (!$this->indexExists('ingredient_inventory', 'ingredient_inv_branch_ingredient_index')) {
                $table->index(['branch_id', 'ingredient_id'], 'ingredient_inv_branch_ingredient_index');
            }
        });

        // Add indexes to products table
        Schema::table('products', function (Blueprint $table) {
            if (!$this->indexExists('products', 'products_category_id_is_active_index')) {
                $table->index(['category_id', 'is_active'], 'products_category_id_is_active_index');
            }
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndex('sales_branch_id_created_at_index');
            $table->dropIndex('sales_user_id_created_at_index');
            $table->dropIndex('sales_status_index');
            $table->dropIndex('sales_payment_method_index');
        });

        Schema::table('sales_items', function (Blueprint $table) {
            $table->dropIndex('sales_items_product_id_index');
        });

        Schema::table('inventory', function (Blueprint $table) {
            $table->dropIndex('inventory_branch_product_index');
        });

        Schema::table('ingredient_inventory', function (Blueprint $table) {
            $table->dropIndex('ingredient_inv_branch_ingredient_index');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_category_id_is_active_index');
        });
    }
};
