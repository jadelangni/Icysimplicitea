<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('id_number', 32)->nullable()->unique()->after('id');
        });

        $dailySequence = [];

        DB::table('users')
            ->orderBy('created_at')
            ->orderBy('id')
            ->select('id', 'created_at')
            ->get()
            ->each(function ($user) use (&$dailySequence): void {
                $date = $user->created_at ? date('ymd', strtotime($user->created_at)) : date('ymd');
                $dailySequence[$date] = ($dailySequence[$date] ?? 0) + 1;

                $idNumber = $date . str_pad((string) $dailySequence[$date], 2, '0', STR_PAD_LEFT);

                DB::table('users')
                    ->where('id', $user->id)
                    ->update(['id_number' => $idNumber]);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['id_number']);
            $table->dropColumn('id_number');
        });
    }
};
