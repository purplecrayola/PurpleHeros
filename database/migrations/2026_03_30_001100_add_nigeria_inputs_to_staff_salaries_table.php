<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('staff_salaries', function (Blueprint $table) {
            $table->string('tax_station')->nullable()->after('salary');
            $table->unsignedTinyInteger('worked_days')->nullable()->after('tax_station');
            $table->unsignedTinyInteger('total_working_days')->nullable()->after('worked_days');
            $table->decimal('unpaid_days', 8, 2)->nullable()->after('total_working_days');
            $table->decimal('salary_advance', 14, 2)->nullable()->after('unpaid_days');
            $table->decimal('kpi_other_deductions', 14, 2)->nullable()->after('salary_advance');
            $table->decimal('annual_rent', 14, 2)->nullable()->after('kpi_other_deductions');
            $table->decimal('non_taxable_reimbursement', 14, 2)->nullable()->after('annual_rent');
        });
    }

    public function down(): void
    {
        Schema::table('staff_salaries', function (Blueprint $table) {
            $table->dropColumn([
                'tax_station',
                'worked_days',
                'total_working_days',
                'unpaid_days',
                'salary_advance',
                'kpi_other_deductions',
                'annual_rent',
                'non_taxable_reimbursement',
            ]);
        });
    }
};
