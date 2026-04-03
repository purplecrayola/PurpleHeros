<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('annual_performance_reviews', function (Blueprint $table) {
            $table->id();
            $table->string('user_id');
            $table->unsignedSmallInteger('review_year');
            $table->text('self_summary')->nullable();
            $table->text('manager_summary')->nullable();
            $table->decimal('self_objectives_score', 5, 2)->nullable();
            $table->decimal('self_values_score', 5, 2)->nullable();
            $table->decimal('manager_objectives_score', 5, 2)->nullable();
            $table->decimal('manager_values_score', 5, 2)->nullable();
            $table->decimal('manager_final_score', 5, 2)->nullable();
            $table->timestamp('self_submitted_at')->nullable();
            $table->timestamp('manager_submitted_at')->nullable();
            $table->enum('status', ['draft', 'self_submitted', 'manager_finalized'])->default('draft');
            $table->string('manager_user_id')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'review_year']);
            $table->index(['review_year', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('annual_performance_reviews');
    }
};
