<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_policy_set_id')->nullable()->constrained('payroll_policy_sets')->nullOnDelete();
            $table->string('run_code')->unique();
            $table->unsignedSmallInteger('period_year');
            $table->unsignedTinyInteger('period_month');
            $table->date('period_start');
            $table->date('period_end');
            $table->unsignedTinyInteger('default_worked_days')->default(21);
            $table->unsignedTinyInteger('default_total_working_days')->default(21);
            $table->enum('status', ['draft', 'calculated', 'approved', 'posted', 'locked'])->default('draft');
            $table->timestamp('calculated_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('posted_at')->nullable();
            $table->timestamp('locked_at')->nullable();
            $table->string('created_by_user_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['period_year', 'period_month']);
            $table->index(['status', 'period_year', 'period_month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_runs');
    }
};
