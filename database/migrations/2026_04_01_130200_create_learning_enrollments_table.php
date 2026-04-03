<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('learning_enrollments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('learning_course_id')->constrained('learning_courses')->cascadeOnDelete();
            $table->string('user_id', 50);
            $table->string('assigned_by_user_id', 50)->nullable();
            $table->dateTime('assigned_at')->nullable();
            $table->dateTime('due_at')->nullable();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('last_activity_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->string('status', 32)->default('not_started'); // not_started|in_progress|completed|overdue
            $table->decimal('completion_percent', 5, 2)->default(0);
            $table->boolean('is_mandatory')->default(false);
            $table->timestamps();

            $table->unique(['learning_course_id', 'user_id'], 'learning_enrollments_course_user_unique');
            $table->index(['user_id', 'status'], 'learning_enrollments_user_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('learning_enrollments');
    }
};

