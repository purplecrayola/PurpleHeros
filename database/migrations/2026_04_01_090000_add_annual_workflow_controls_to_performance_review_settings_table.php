<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('performance_review_settings', function (Blueprint $table) {
            $table->boolean('annual_stage_manager_submit_required')->default(true)->after('annual_section_values_enabled');
            $table->boolean('annual_stage_calibration_enabled')->default(false)->after('annual_stage_manager_submit_required');
            $table->boolean('annual_stage_joint_review_enabled')->default(true)->after('annual_stage_calibration_enabled');
            $table->boolean('annual_stage_employee_ack_required')->default(true)->after('annual_stage_joint_review_enabled');
            $table->boolean('annual_allow_admin_manual_progress')->default(true)->after('annual_stage_employee_ack_required');
        });
    }

    public function down(): void
    {
        Schema::table('performance_review_settings', function (Blueprint $table) {
            $table->dropColumn([
                'annual_stage_manager_submit_required',
                'annual_stage_calibration_enabled',
                'annual_stage_joint_review_enabled',
                'annual_stage_employee_ack_required',
                'annual_allow_admin_manual_progress',
            ]);
        });
    }
};

