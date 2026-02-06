<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            
            // Inventory type (product or ingredient)
            $table->enum('inventory_type', ['product', 'ingredient']);
            $table->foreignId('product_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('ingredient_id')->nullable()->constrained()->onDelete('set null');
            
            // Movement details
            $table->string('movement_type', 50); // sale, void, restock, adjustment, transfer_out, transfer_in, return, waste, initial
            $table->decimal('quantity_before', 15, 4);
            $table->decimal('quantity_change', 15, 4); // negative for deductions
            $table->decimal('quantity_after', 15, 4);
            $table->string('unit', 20)->nullable();
            
            // Reference to source record
            $table->string('reference_type', 100)->nullable(); // App\Models\Sale, etc.
            $table->unsignedBigInteger('reference_id')->nullable();
            
            // Audit and notes
            $table->string('reason_code', 50)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            
            // Cost tracking for COGS
            $table->decimal('cost_per_unit', 15, 4)->nullable();
            $table->decimal('total_cost', 15, 4)->nullable();
            
            $table->timestamps();
            
            // Indexes for common queries
            $table->index(['branch_id', 'inventory_type', 'product_id']);
            $table->index(['branch_id', 'inventory_type', 'ingredient_id']);
            $table->index(['movement_type', 'created_at']);
            $table->index(['reference_type', 'reference_id']);
            $table->index('created_at');
        });

        // Add idempotency key to sales table for duplicate submission protection
        Schema::table('sales', function (Blueprint $table) {
            $table->string('idempotency_key', 64)->nullable()->unique()->after('id');
            $table->index('idempotency_key');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndex(['idempotency_key']);
            $table->dropColumn('idempotency_key');
        });
        
        Schema::dropIfExists('stock_movements');
    }
};
