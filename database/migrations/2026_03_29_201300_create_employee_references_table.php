<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_references', function (Blueprint $table): void {
            $table->id();
            $table->string('user_id');
            $table->string('referee_name');
            $table->string('relationship')->nullable();
            $table->string('company_name')->nullable();
            $table->string('job_title')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('years_known')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->text('verification_feedback')->nullable();
            $table->string('verified_by_user_id')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->string('created_by_user_id')->nullable();
            $table->timestamps();

            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_references');
    }
};
