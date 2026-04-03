<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleTypeUserSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'Super Admin',
            'Admin',
            'HR Manager',
            'Payroll Admin',
            'Operations Manager',
            'Reports Analyst',
            'Employee',
            'Normal User',
            'Client',
        ];

        foreach ($roles as $role) {
            DB::table('role_type_users')->updateOrInsert(
                ['role_type' => $role],
                ['updated_at' => now(), 'created_at' => now()]
            );
        }
    }
}
