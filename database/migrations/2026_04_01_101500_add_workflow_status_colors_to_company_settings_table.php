<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_settings', function (Blueprint $table): void {
            $table->string('workflow_current_color', 7)->nullable()->after('sidebar_muted_text_color');
            $table->string('workflow_completed_color', 7)->nullable()->after('workflow_current_color');
            $table->string('workflow_pending_color', 7)->nullable()->after('workflow_completed_color');
        });
    }

    public function down(): void
    {
        Schema::table('company_settings', function (Blueprint $table): void {
            $table->dropColumn([
                'workflow_current_color',
                'workflow_completed_color',
                'workflow_pending_color',
            ]);
        });
    }
};

