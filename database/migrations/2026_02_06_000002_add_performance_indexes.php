<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
	/**
	 * Run the migrations.
	 */
	public function up(): void
	{
		// This migration file was previously empty, which caused Forge deployments
		// to fail while resolving the expected migration class.
	}

	/**
	 * Reverse the migrations.
	 */
	public function down(): void
	{
		// No-op: no schema changes were applied in this migration.
	}
};
