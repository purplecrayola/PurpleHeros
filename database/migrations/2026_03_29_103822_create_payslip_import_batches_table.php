<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payslip_import_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_run_id')->nullable()->constrained('payroll_runs')->nullOnDelete();
            $table->string('title');
            $table->unsignedSmallInteger('period_year')->nullable();
            $table->unsignedTinyInteger('period_month')->nullable();
            $table->string('source_file_name')->nullable();
            $table->string('uploaded_by_user_id')->nullable();
            $table->enum('status', ['draft', 'processed', 'failed'])->default('draft');
            $table->unsignedInteger('processed_rows')->default(0);
            $table->unsignedInteger('failed_rows')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['period_year', 'period_month', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payslip_import_batches');
    }
};
