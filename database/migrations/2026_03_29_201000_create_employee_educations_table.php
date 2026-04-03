<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_educations', function (Blueprint $table): void {
            $table->id();
            $table->string('user_id');
            $table->string('institution')->nullable();
            $table->string('degree')->nullable();
            $table->string('field_of_study')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('grade')->nullable();
            $table->string('certificate_file_path')->nullable();
            $table->string('created_by_user_id')->nullable();
            $table->timestamps();

            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_educations');
    }
};
