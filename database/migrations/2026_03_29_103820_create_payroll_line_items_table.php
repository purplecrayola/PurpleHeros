<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_line_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_run_employee_id')->constrained('payroll_run_employees')->cascadeOnDelete();
            $table->enum('line_type', ['earning', 'deduction', 'reimbursement', 'employer_cost']);
            $table->string('code');
            $table->string('label');
            $table->boolean('is_taxable')->default(false);
            $table->decimal('amount', 15, 2)->default(0);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['payroll_run_employee_id', 'line_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_line_items');
    }
};
