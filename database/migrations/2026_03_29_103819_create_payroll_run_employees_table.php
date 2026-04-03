<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_run_employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_run_id')->constrained('payroll_runs')->cascadeOnDelete();
            $table->string('user_id');
            $table->string('employee_name');
            $table->string('designation')->nullable();
            $table->string('department')->nullable();
            $table->string('tax_station')->nullable();
            $table->string('account_number')->nullable();
            $table->enum('source', ['generated', 'imported'])->default('generated');
            $table->enum('status', ['draft', 'finalized'])->default('draft');
            $table->json('input_payload')->nullable();
            $table->json('computed_payload')->nullable();
            $table->decimal('gross_salary', 15, 2)->default(0);
            $table->decimal('total_taxable_earnings', 15, 2)->default(0);
            $table->decimal('total_deductions', 15, 2)->default(0);
            $table->decimal('net_salary', 15, 2)->default(0);
            $table->decimal('total_paid', 15, 2)->default(0);
            $table->timestamps();

            $table->unique(['payroll_run_id', 'user_id']);
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_run_employees');
    }
};
