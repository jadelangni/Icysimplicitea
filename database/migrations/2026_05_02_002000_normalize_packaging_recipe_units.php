<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('ingredients', 'recipe_unit')) {
            return;
        }

        DB::table('ingredients')
            ->whereIn(DB::raw('LOWER(unit)'), ['pack', 'packs', 'bag', 'bags', 'box', 'boxes'])
            ->where(function ($query) {
                $query->whereNull('recipe_unit')
                    ->orWhereIn(DB::raw('LOWER(recipe_unit)'), ['pack', 'packs', 'bag', 'bags', 'box', 'boxes']);
            })
            ->update(['recipe_unit' => 'g']);

        DB::table('ingredients')
            ->whereIn(DB::raw('LOWER(unit)'), ['can', 'cans', 'bottle', 'bottles'])
            ->where(function ($query) {
                $query->whereNull('recipe_unit')
                    ->orWhereIn(DB::raw('LOWER(recipe_unit)'), ['can', 'cans', 'bottle', 'bottles']);
            })
            ->update(['recipe_unit' => 'ml']);

        DB::table('ingredients')
            ->whereNull('recipe_units_per_inventory_unit')
            ->update(['recipe_units_per_inventory_unit' => 1]);
    }

    public function down(): void
    {
        //
    }
};
