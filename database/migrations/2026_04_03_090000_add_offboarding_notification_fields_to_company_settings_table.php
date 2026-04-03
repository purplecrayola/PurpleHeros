<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_settings', function (Blueprint $table) {
            $table->boolean('offboarding_notify_on_planned')->default(true)->after('learning_completion_body');
            $table->boolean('offboarding_notify_on_completed')->default(true)->after('offboarding_notify_on_planned');
            $table->string('offboarding_planned_subject')->nullable()->after('offboarding_notify_on_completed');
            $table->text('offboarding_planned_body')->nullable()->after('offboarding_planned_subject');
            $table->string('offboarding_completed_subject')->nullable()->after('offboarding_planned_body');
            $table->text('offboarding_completed_body')->nullable()->after('offboarding_completed_subject');
        });
    }

    public function down(): void
    {
        Schema::table('company_settings', function (Blueprint $table) {
            $table->dropColumn([
                'offboarding_notify_on_planned',
                'offboarding_notify_on_completed',
                'offboarding_planned_subject',
                'offboarding_planned_body',
                'offboarding_completed_subject',
                'offboarding_completed_body',
            ]);
        });
    }
};

