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
        Schema::create('permission_overrides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('requested_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->foreignId('sale_id')->nullable()->constrained()->onDelete('set null');
            $table->string('action'); // e.g., 'void_sale', 'apply_discount', 'price_override', 'open_drawer'
            $table->enum('status', ['pending', 'approved', 'denied', 'expired'])->default('pending');
            $table->decimal('original_amount', 10, 2)->nullable();
            $table->decimal('new_amount', 10, 2)->nullable();
            $table->decimal('discount_percent', 5, 2)->nullable();
            $table->text('reason')->nullable();
            $table->text('denial_reason')->nullable();
            $table->timestamp('requested_at');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            
            // Index for quick lookups
            $table->index(['status', 'requested_at']);
            $table->index(['requested_by', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permission_overrides');
    }
};
