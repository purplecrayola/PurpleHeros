<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timesheet_entries', function (Blueprint $table) {
            $table->id();
            $table->string('user_id');
            $table->date('work_date');
            $table->string('project_name');
            $table->unsignedInteger('assigned_hours')->default(0);
            $table->unsignedInteger('worked_hours')->default(0);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'work_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timesheet_entries');
    }
};
