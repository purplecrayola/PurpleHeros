<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('annual_performance_reviews', function (Blueprint $table) {
            $table->string('workflow_step')->default('self_draft')->after('status');
            $table->text('workflow_notes')->nullable()->after('workflow_step');
            $table->timestamp('calibration_completed_at')->nullable()->after('manager_submitted_at');
            $table->timestamp('joint_review_at')->nullable()->after('calibration_completed_at');
            $table->timestamp('employee_acknowledged_at')->nullable()->after('joint_review_at');
            $table->timestamp('finalized_at')->nullable()->after('employee_acknowledged_at');
            $table->string('finalized_by_user_id')->nullable()->after('finalized_at');
            $table->index('workflow_step');
        });

        DB::table('annual_performance_reviews')
            ->where('status', 'manager_finalized')
            ->update([
                'workflow_step' => 'finalized',
                'finalized_at' => DB::raw('COALESCE(manager_submitted_at, updated_at)'),
            ]);

        DB::table('annual_performance_reviews')
            ->where('status', 'self_submitted')
            ->where('workflow_step', 'self_draft')
            ->update(['workflow_step' => 'manager_draft']);
    }

    public function down(): void
    {
        Schema::table('annual_performance_reviews', function (Blueprint $table) {
            $table->dropIndex(['workflow_step']);
            $table->dropColumn([
                'workflow_step',
                'workflow_notes',
                'calibration_completed_at',
                'joint_review_at',
                'employee_acknowledged_at',
                'finalized_at',
                'finalized_by_user_id',
            ]);
        });
    }
};

