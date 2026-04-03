<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('learning_courses', function (Blueprint $table): void {
            $table->id();
            $table->string('course_code', 60)->nullable()->unique();
            $table->string('title');
            $table->string('slug')->nullable()->unique();
            $table->longText('description')->nullable();
            $table->string('delivery_mode', 32)->default('virtual'); // physical|virtual|self_paced|other
            $table->string('visibility_status', 32)->default('published'); // draft|published|archived
            $table->boolean('catalog_visible')->default(true);
            $table->string('status', 32)->default('active'); // active|inactive
            $table->string('join_link')->nullable();
            $table->string('venue')->nullable();
            $table->dateTime('start_at')->nullable();
            $table->dateTime('end_at')->nullable();
            $table->unsignedInteger('estimated_duration_minutes')->nullable();
            $table->string('created_by_user_id', 50)->nullable();
            $table->timestamps();

            $table->index(['catalog_visible', 'visibility_status', 'status'], 'learning_courses_catalog_visibility_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('learning_courses');
    }
};

