<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ForgeAdminEmployeeSeeder extends Seeder
{
    /**
     * Seed or update default admin and employee users for production.
     */
    public function run(): void
    {
        $firstBranchId = Branch::query()->orderBy('id')->value('id');

        $adminEmail = env('FORGE_ADMIN_EMAIL', 'admin@simplicitea.com');
        $adminPassword = env('FORGE_ADMIN_PASSWORD', 'ChangeMeAdmin123!');

        $employeeEmail = env('FORGE_EMPLOYEE_EMAIL', 'employee@simplicitea.com');
        $employeePassword = env('FORGE_EMPLOYEE_PASSWORD', 'ChangeMeEmployee123!');

        User::updateOrCreate(
            ['email' => $adminEmail],
            [
                'name' => 'Forge Admin',
                'alert_email' => $adminEmail,
                'password' => Hash::make($adminPassword),
                'role' => 'admin',
                'branch_id' => $firstBranchId,
                'is_active' => true,
                'must_change_password' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => $employeeEmail],
            [
                'name' => 'Forge Employee',
                'alert_email' => $employeeEmail,
                'password' => Hash::make($employeePassword),
                'role' => 'cashier',
                'branch_id' => $firstBranchId,
                'is_active' => true,
                'must_change_password' => true,
            ]
        );
    }
}