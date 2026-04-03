<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('performance_objectives', function (Blueprint $table) {
            $table->id();
            $table->string('user_id');
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('period_type', ['quarterly', 'biannual', 'annual']);
            $table->date('period_start');
            $table->date('period_end');
            $table->decimal('weight', 5, 2)->nullable();
            $table->enum('source', ['employee', 'manager', 'super_admin'])->default('employee');
            $table->string('created_by_user_id');
            $table->enum('status', ['draft', 'active', 'completed', 'cancelled'])->default('active');
            $table->timestamps();

            $table->index(['user_id', 'period_type']);
            $table->index(['period_start', 'period_end']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('performance_objectives');
    }
};
