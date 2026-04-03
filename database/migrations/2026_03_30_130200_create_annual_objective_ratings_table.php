<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('annual_objective_ratings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('annual_performance_review_id');
            $table->unsignedBigInteger('performance_objective_id');
            $table->unsignedTinyInteger('self_rating')->nullable();
            $table->text('self_comment')->nullable();
            $table->unsignedTinyInteger('manager_rating')->nullable();
            $table->text('manager_comment')->nullable();
            $table->timestamps();

            $table->unique(['annual_performance_review_id', 'performance_objective_id'], 'annual_obj_rating_unique');
            $table->index('performance_objective_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('annual_objective_ratings');
    }
};
