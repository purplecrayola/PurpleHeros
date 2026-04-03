<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('annual_review_workflow_events')) {
            Schema::create('annual_review_workflow_events', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('annual_performance_review_id');
                $table->string('from_step', 64)->nullable();
                $table->string('to_step', 64)->nullable();
                $table->string('action', 64);
                $table->string('actor_user_id', 50)->nullable();
                $table->string('actor_role', 100)->nullable();
                $table->text('notes')->nullable();
                $table->json('notified_emails')->nullable();
                $table->timestamps();
            });
        }

        try {
            Schema::table('annual_review_workflow_events', function (Blueprint $table): void {
                $table->foreign('annual_performance_review_id', 'arwe_review_fk')
                    ->references('id')
                    ->on('annual_performance_reviews')
                    ->cascadeOnDelete();
            });
        } catch (\Throwable) {
            // ignore if FK already exists in partially applied environments
        }

        try {
            Schema::table('annual_review_workflow_events', function (Blueprint $table): void {
                $table->index(['annual_performance_review_id', 'created_at'], 'annual_review_workflow_events_review_created_idx');
            });
        } catch (\Throwable) {
            // ignore if index already exists in partially applied environments
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('annual_review_workflow_events');
    }
};
