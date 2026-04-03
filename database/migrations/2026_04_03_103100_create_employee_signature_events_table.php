<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_signature_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_signature_request_id')
                ->constrained('employee_signature_requests')
                ->cascadeOnDelete();
            $table->string('event_type', 50);
            $table->json('event_payload')->nullable();
            $table->string('ip_address', 64)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_signature_events');
    }
};

