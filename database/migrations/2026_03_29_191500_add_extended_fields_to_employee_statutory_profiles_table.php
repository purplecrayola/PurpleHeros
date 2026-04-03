<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_statutory_profiles', function (Blueprint $table) {
            $table->string('salary_basis')->nullable()->after('tax_residency_state');
            $table->string('payment_type')->nullable()->after('salary_basis');

            $table->decimal('pf_contribution_rate_percent', 5, 2)->nullable()->after('pf_number');
            $table->decimal('pf_additional_rate_percent', 5, 2)->nullable()->after('pf_contribution_rate_percent');
            $table->decimal('esi_contribution_rate_percent', 5, 2)->nullable()->after('esi_number');
            $table->decimal('esi_additional_rate_percent', 5, 2)->nullable()->after('esi_contribution_rate_percent');
        });
    }

    public function down(): void
    {
        Schema::table('employee_statutory_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'salary_basis',
                'payment_type',
                'pf_contribution_rate_percent',
                'pf_additional_rate_percent',
                'esi_contribution_rate_percent',
                'esi_additional_rate_percent',
            ]);
        });
    }
};

