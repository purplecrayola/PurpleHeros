<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_statutory_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('user_id')->unique();
            $table->string('tax_station')->nullable();
            $table->string('tax_residency_state')->nullable();

            $table->boolean('pension_enabled')->default(true);
            $table->decimal('employee_pension_rate_percent', 5, 2)->nullable();
            $table->decimal('employer_pension_rate_percent', 5, 2)->nullable();
            $table->string('pension_pin')->nullable();

            $table->boolean('nhf_enabled')->default(false);
            $table->decimal('nhf_rate_percent', 5, 2)->nullable();
            $table->decimal('nhf_base_cap', 14, 2)->nullable();
            $table->string('nhf_number')->nullable();

            $table->decimal('annual_rent', 14, 2)->default(0);
            $table->decimal('other_statutory_deductions', 14, 2)->default(0);
            $table->decimal('default_non_taxable_reimbursement', 14, 2)->default(0);

            $table->boolean('pf_enabled')->default(false);
            $table->string('pf_number')->nullable();
            $table->boolean('esi_enabled')->default(false);
            $table->string('esi_number')->nullable();

            $table->string('created_by_user_id')->nullable();
            $table->timestamps();

            $table->index('tax_station');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_statutory_profiles');
    }
};

