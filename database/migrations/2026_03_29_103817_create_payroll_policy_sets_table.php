<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_policy_sets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('country_code', 2)->default('NG');
            $table->string('state_code', 16)->nullable();
            $table->string('currency_code', 3)->default('NGN');
            $table->boolean('is_active')->default(false);
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->json('settings')->nullable();
            $table->string('created_by_user_id')->nullable();
            $table->timestamps();

            $table->index(['country_code', 'state_code']);
            $table->index(['is_active', 'effective_from']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_policy_sets');
    }
};
