<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('performance_review_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('objective_weight')->default(70);
            $table->unsignedTinyInteger('values_weight')->default(30);
            $table->boolean('allow_employee_objectives')->default(true);
            $table->boolean('allow_manager_objectives')->default(true);
            $table->unsignedTinyInteger('monthly_update_due_day')->default(28);
            $table->unsignedTinyInteger('weekly_update_due_weekday')->default(5); // 1=Mon ... 7=Sun
            $table->boolean('annual_section_objectives_enabled')->default(true);
            $table->boolean('annual_section_values_enabled')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('performance_review_settings');
    }
};
