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
        Schema::create('staff_attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['clock_in', 'clock_out']);
            $table->string('selfie_path')->nullable(); // Path to selfie image
            $table->string('ip_address', 45)->nullable();
            $table->text('notes')->nullable();
            $table->decimal('latitude', 10, 8)->nullable(); // GPS coordinates if available
            $table->decimal('longitude', 11, 8)->nullable();
            $table->timestamp('recorded_at');
            $table->timestamps();
            
            // Index for quick lookups
            $table->index(['user_id', 'recorded_at']);
            $table->index(['branch_id', 'recorded_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_attendance');
    }
};
