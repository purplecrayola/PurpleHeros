<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_offboardings', function (Blueprint $table) {
            $table->id();
            $table->string('user_id')->unique();
            $table->string('offboarding_status')->default('not_started');
            $table->string('offboarding_type')->nullable();
            $table->date('notice_submitted_on')->nullable();
            $table->date('last_working_day')->nullable();
            $table->date('exit_interview_date')->nullable();
            $table->boolean('exit_interview_completed')->default(false);
            $table->boolean('knowledge_transfer_completed')->default(false);
            $table->boolean('assets_returned')->default(false);
            $table->boolean('access_revoked')->default(false);
            $table->boolean('final_settlement_completed')->default(false);
            $table->boolean('rehire_eligible')->default(false);
            $table->text('offboarding_reason')->nullable();
            $table->text('offboarding_notes')->nullable();
            $table->string('initiated_by_user_id')->nullable();
            $table->string('completed_by_user_id')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['offboarding_status', 'last_working_day']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_offboardings');
    }
};

