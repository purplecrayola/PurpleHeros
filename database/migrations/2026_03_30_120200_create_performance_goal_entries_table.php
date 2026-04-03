<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('performance_goal_entries', function (Blueprint $table) {
            $table->id();
            $table->string('user_id');
            $table->enum('period_type', ['weekly', 'monthly']);
            $table->unsignedSmallInteger('period_year');
            $table->unsignedTinyInteger('period_number'); // week number (1-53) or month (1-12)
            $table->string('title');
            $table->text('planned_tasks');
            $table->text('end_period_update')->nullable();
            $table->unsignedTinyInteger('completion_percent')->nullable();
            $table->text('blockers')->nullable();
            $table->text('manager_comment')->nullable();
            $table->enum('status', ['draft', 'submitted', 'reviewed'])->default('draft');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('manager_reviewed_at')->nullable();
            $table->string('created_by_user_id');
            $table->timestamps();

            $table->index(['user_id', 'period_type', 'period_year', 'period_number'], 'perf_goal_period_idx');
            $table->index(['status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('performance_goal_entries');
    }
};
