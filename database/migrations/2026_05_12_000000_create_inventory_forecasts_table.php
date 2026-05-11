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
        Schema::create('inventory_forecasts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->date('forecast_date');
            $table->integer('forecasted_quantity')->default(0);
            $table->decimal('confidence_level', 5, 2)->default(100.00)->comment('Forecast confidence level 0-100');
            $table->timestamps();

            // Indexes for better query performance
            $table->index(['product_id', 'branch_id']);
            $table->index('forecast_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_forecasts');
    }
};
