<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payslips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_run_id')->nullable()->constrained('payroll_runs')->nullOnDelete();
            $table->foreignId('payroll_run_employee_id')->nullable()->constrained('payroll_run_employees')->nullOnDelete();
            $table->string('user_id');
            $table->unsignedSmallInteger('period_year');
            $table->unsignedTinyInteger('period_month');
            $table->enum('source', ['generated', 'imported', 'uploaded'])->default('generated');
            $table->string('file_path')->nullable();
            $table->json('payload')->nullable();
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->boolean('is_locked')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'period_year', 'period_month']);
            $table->index(['period_year', 'period_month', 'source']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payslips');
    }
};
