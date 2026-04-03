<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_signature_signers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_signature_request_id')
                ->constrained('employee_signature_requests')
                ->cascadeOnDelete();
            $table->string('role_label', 120)->nullable();
            $table->string('signer_name')->nullable();
            $table->string('signer_email')->nullable();
            $table->unsignedInteger('sign_order')->default(1);
            $table->string('signature_field_key', 120)->nullable();
            $table->unsignedInteger('page_number')->default(1);
            $table->string('status', 20)->default('pending');
            $table->string('token', 120)->unique();
            $table->timestamp('signed_at')->nullable();
            $table->text('signed_acknowledgement')->nullable();
            $table->string('signed_hash', 128)->nullable();
            $table->string('completed_by_user_id')->nullable();
            $table->timestamps();

            $table->index(['employee_signature_request_id', 'sign_order'], 'signature_signers_request_order_idx');
            $table->index(['status', 'signer_email'], 'signature_signers_status_email_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_signature_signers');
    }
};
