<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('learning_assets', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('learning_course_id')->constrained('learning_courses')->cascadeOnDelete();
            $table->string('asset_type', 32)->default('pdf'); // pdf|audio|video|link|file
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('file_path')->nullable();
            $table->string('external_url')->nullable();
            $table->unsignedInteger('sort_order')->default(1);
            $table->boolean('is_required')->default(true);
            $table->unsignedInteger('duration_seconds')->nullable();
            $table->unsignedInteger('pages_count')->nullable();
            $table->string('status', 32)->default('active');
            $table->timestamps();

            $table->index(['learning_course_id', 'sort_order'], 'learning_assets_course_sort_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('learning_assets');
    }
};

