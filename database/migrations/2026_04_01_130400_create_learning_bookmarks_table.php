<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('learning_bookmarks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('learning_enrollment_id')->constrained('learning_enrollments')->cascadeOnDelete();
            $table->foreignId('learning_asset_id')->nullable()->constrained('learning_assets')->nullOnDelete();
            $table->unsignedInteger('page_number')->nullable();
            $table->unsignedInteger('position_seconds')->nullable();
            $table->string('label')->nullable();
            $table->text('note')->nullable();
            $table->string('created_by_user_id', 50)->nullable();
            $table->timestamps();

            $table->index(['learning_enrollment_id', 'created_at'], 'learning_bookmarks_enrollment_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('learning_bookmarks');
    }
};

