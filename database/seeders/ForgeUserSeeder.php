<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ForgeUserSeeder extends Seeder
{
    /**
     * Seed default Forge users (admin + employee/cashier).
     */
    public function run(): void
    {
        $this->call([
            ForgeAdminEmployeeSeeder::class,
        ]);
    }
}