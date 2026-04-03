<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_signature_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_onboarding_id')->nullable()->constrained('employee_onboardings')->nullOnDelete();
            $table->string('user_id');
            $table->string('document_type', 30); // offer|contract|other
            $table->string('document_path')->nullable();
            $table->string('status', 20)->default('pending'); // pending|signed|cancelled|expired
            $table->string('signer_name')->nullable();
            $table->string('signer_email')->nullable();
            $table->string('token', 120)->unique();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('signed_at')->nullable();
            $table->text('signed_acknowledgement')->nullable();
            $table->string('signed_hash', 128)->nullable();
            $table->string('initiated_by_user_id')->nullable();
            $table->string('completed_by_user_id')->nullable();
            $table->timestamps();

            $table->index(['status', 'document_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_signature_requests');
    }
};

