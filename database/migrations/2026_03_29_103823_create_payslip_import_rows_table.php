<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payslip_import_rows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payslip_import_batch_id')->constrained('payslip_import_batches')->cascadeOnDelete();
            $table->foreignId('payroll_run_employee_id')->nullable()->constrained('payroll_run_employees')->nullOnDelete();
            $table->string('user_id')->nullable();
            $table->string('employee_name')->nullable();
            $table->unsignedSmallInteger('period_year')->nullable();
            $table->unsignedTinyInteger('period_month')->nullable();
            $table->decimal('gross_salary', 15, 2)->nullable();
            $table->decimal('total_deductions', 15, 2)->nullable();
            $table->decimal('net_salary', 15, 2)->nullable();
            $table->string('file_path')->nullable();
            $table->json('payload')->nullable();
            $table->text('error_message')->nullable();
            $table->enum('row_status', ['pending', 'imported', 'failed'])->default('pending');
            $table->timestamps();

            $table->index(['user_id', 'period_year', 'period_month']);
            $table->index(['row_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payslip_import_rows');
    }
};
