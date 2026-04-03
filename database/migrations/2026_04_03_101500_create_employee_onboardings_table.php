<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_onboardings', function (Blueprint $table) {
            $table->id();
            $table->string('user_id')->unique();
            $table->string('onboarding_status')->default('draft');

            $table->string('offer_status')->default('not_started');
            $table->string('offer_document_path')->nullable();
            $table->string('offer_sign_provider')->nullable();
            $table->string('offer_signature_request_id')->nullable();
            $table->date('offer_sent_at')->nullable();
            $table->date('offer_signed_at')->nullable();

            $table->string('contract_status')->default('not_started');
            $table->string('contract_document_path')->nullable();
            $table->string('contract_sign_provider')->nullable();
            $table->string('contract_signature_request_id')->nullable();
            $table->date('contract_sent_at')->nullable();
            $table->date('contract_signed_at')->nullable();

            $table->string('reference_check_status')->default('pending');
            $table->unsignedInteger('references_total_count')->default(0);
            $table->unsignedInteger('references_verified_count')->default(0);
            $table->string('background_check_status')->default('not_started');
            $table->date('planned_start_date')->nullable();
            $table->timestamp('onboarding_completed_at')->nullable();
            $table->text('onboarding_notes')->nullable();
            $table->string('created_by_user_id')->nullable();
            $table->string('updated_by_user_id')->nullable();
            $table->timestamps();

            $table->index(['onboarding_status', 'planned_start_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_onboardings');
    }
};

