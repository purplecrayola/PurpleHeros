<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_documents', function (Blueprint $table): void {
            $table->id();
            $table->string('user_id');
            $table->string('document_type');
            $table->string('title')->nullable();
            $table->string('file_path');
            $table->boolean('is_verified')->default(false);
            $table->text('verification_feedback')->nullable();
            $table->string('verified_by_user_id')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->string('uploaded_by_user_id')->nullable();
            $table->timestamps();

            $table->index('user_id');
            $table->index(['user_id', 'document_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_documents');
    }
};
