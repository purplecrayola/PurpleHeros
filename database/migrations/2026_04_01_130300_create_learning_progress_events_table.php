<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('learning_progress_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('learning_enrollment_id')->constrained('learning_enrollments')->cascadeOnDelete();
            $table->foreignId('learning_asset_id')->nullable()->constrained('learning_assets')->nullOnDelete();
            $table->string('event_type', 40); // start|open_asset|view_page|listen|bookmark|complete
            $table->decimal('progress_percent', 5, 2)->nullable();
            $table->unsignedInteger('current_page')->nullable();
            $table->unsignedInteger('total_pages')->nullable();
            $table->unsignedInteger('position_seconds')->nullable();
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->json('meta')->nullable();
            $table->string('created_by_user_id', 50)->nullable();
            $table->timestamps();

            $table->index(['learning_enrollment_id', 'created_at'], 'learning_progress_events_enrollment_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('learning_progress_events');
    }
};

