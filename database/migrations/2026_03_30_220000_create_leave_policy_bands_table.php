<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_policy_bands', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('category')->default('other');
            $table->unsignedInteger('annual_entitlement_days')->nullable();
            $table->boolean('carry_forward_enabled')->default(false);
            $table->unsignedInteger('carry_forward_cap_days')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        DB::table('leave_policy_bands')->insert([
            [
                'name' => 'Annual Leave',
                'category' => 'annual',
                'annual_entitlement_days' => 20,
                'carry_forward_enabled' => true,
                'carry_forward_cap_days' => 5,
                'is_active' => true,
                'sort_order' => 10,
                'description' => 'Standard annual leave allocation.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Sick Leave',
                'category' => 'sick',
                'annual_entitlement_days' => 10,
                'carry_forward_enabled' => false,
                'carry_forward_cap_days' => null,
                'is_active' => true,
                'sort_order' => 20,
                'description' => 'Leave for health-related cases.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Maternity Leave',
                'category' => 'maternity',
                'annual_entitlement_days' => 120,
                'carry_forward_enabled' => false,
                'carry_forward_cap_days' => null,
                'is_active' => true,
                'sort_order' => 30,
                'description' => 'Leave for maternity support.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Unpaid Leave',
                'category' => 'unpaid',
                'annual_entitlement_days' => null,
                'carry_forward_enabled' => false,
                'carry_forward_cap_days' => null,
                'is_active' => true,
                'sort_order' => 40,
                'description' => 'Approved leave without pay.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_policy_bands');
    }
};
