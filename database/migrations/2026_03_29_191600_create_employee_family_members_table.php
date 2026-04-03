<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_family_members', function (Blueprint $table) {
            $table->id();
            $table->string('user_id')->index();
            $table->string('name')->nullable();
            $table->string('relationship')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('phone')->nullable();
            $table->boolean('is_next_of_kin')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_family_members');
    }
};

