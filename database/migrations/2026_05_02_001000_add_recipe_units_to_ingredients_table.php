<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ingredients', function (Blueprint $table) {
            $table->string('recipe_unit')->nullable()->after('unit');
            $table->decimal('recipe_units_per_inventory_unit', 12, 4)->default(1)->after('recipe_unit');
        });

        DB::table('ingredients')
            ->whereIn(DB::raw('LOWER(unit)'), ['pack', 'packs', 'bag', 'bags', 'box', 'boxes'])
            ->update(['recipe_unit' => 'g']);

        DB::table('ingredients')
            ->whereIn(DB::raw('LOWER(unit)'), ['can', 'cans', 'bottle', 'bottles'])
            ->update(['recipe_unit' => 'ml']);

        DB::table('ingredients')
            ->whereNull('recipe_unit')
            ->update(['recipe_unit' => DB::raw('unit')]);
    }

    public function down(): void
    {
        Schema::table('ingredients', function (Blueprint $table) {
            $table->dropColumn(['recipe_unit', 'recipe_units_per_inventory_unit']);
        });
    }
};
