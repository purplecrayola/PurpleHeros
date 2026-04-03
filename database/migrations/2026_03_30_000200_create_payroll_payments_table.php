<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_run_id')->nullable()->constrained('payroll_runs')->nullOnDelete();
            $table->foreignId('payroll_run_employee_id')->nullable()->constrained('payroll_run_employees')->nullOnDelete();
            $table->string('user_id')->index();
            $table->string('employee_name')->nullable();
            $table->string('provider', 20); // opay|kuda
            $table->string('account_source', 20)->default('primary'); // primary|secondary
            $table->string('bank_name')->nullable();
            $table->string('account_number');
            $table->string('account_name')->nullable();
            $table->decimal('amount', 14, 2);
            $table->string('currency', 10)->default('NGN');
            $table->string('status', 20)->default('pending'); // pending|processing|paid|failed
            $table->string('provider_reference')->nullable();
            $table->string('idempotency_key')->nullable()->unique();
            $table->text('failure_reason')->nullable();
            $table->json('request_payload')->nullable();
            $table->json('provider_response')->nullable();
            $table->string('requested_by_user_id')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_payments');
    }
};

