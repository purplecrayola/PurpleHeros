<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('overtime_entries', function (Blueprint $table) {
            $table->id();
            $table->string('user_id');
            $table->date('ot_date');
            $table->decimal('hours', 5, 2)->default(0);
            $table->string('ot_type');
            $table->string('status')->default('Pending');
            $table->string('approved_by')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'ot_date']);
            $table->index(['status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('overtime_entries');
    }
};
