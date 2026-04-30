<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ingredient_inventory', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ingredient_id')->constrained('ingredients')->onDelete('cascade');
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->decimal('quantity', 12, 2)->default(0);
            $table->decimal('min_stock_level', 12, 2)->default(0);
            $table->timestamps();
            
            // Unique constraint: one inventory record per ingredient per branch
            $table->unique(['ingredient_id', 'branch_id']);
        });

        // Remove branch_id, quantity, and min_stock_level from ingredients table
        // These are now tracked in ingredient_inventory per branch
        Schema::table('ingredients', function (Blueprint $table) {
            // Drop the foreign key if it exists
            if (Schema::hasColumn('ingredients', 'branch_id')) {
                $table->dropForeign(['branch_id']);
                $table->dropIndex(['branch_id']);
                $table->dropColumn('branch_id');
            }
            if (Schema::hasColumn('ingredients', 'quantity')) {
                $table->dropColumn('quantity');
            }
            if (Schema::hasColumn('ingredients', 'min_stock_level')) {
                $table->dropColumn('min_stock_level');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore columns to ingredients table
        Schema::table('ingredients', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('set null');
            $table->decimal('quantity', 12, 2)->default(0);
            $table->decimal('min_stock_level', 12, 2)->default(0);
        });

        Schema::dropIfExists('ingredient_inventory');
    }
};
