<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('export_audits', function (Blueprint $table) {
            $table->id();
            $table->string('user_id')->nullable();
            $table->string('user_email')->nullable();
            $table->string('report_name');
            $table->string('format', 10);
            $table->string('filename');
            $table->string('report_date')->nullable();
            $table->string('employee_search')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('exported_at');
            $table->timestamps();

            $table->index(['report_name', 'format']);
            $table->index(['user_id', 'exported_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('export_audits');
    }
};
