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
        Schema::create('inventory_import_histories', function (Blueprint $table) {
            $table->id();
            $table->timestamp('imported_at');
            $table->foreignId('admin_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('ingredient_id')->constrained('ingredients')->onDelete('cascade');
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->string('supplier');
            $table->string('imported_file');
            $table->decimal('previous_qty', 12, 2);
            $table->decimal('added_qty', 12, 2);
            $table->decimal('final_qty', 12, 2);
            $table->timestamps();

            $table->index(['imported_at', 'branch_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_import_histories');
    }
};
