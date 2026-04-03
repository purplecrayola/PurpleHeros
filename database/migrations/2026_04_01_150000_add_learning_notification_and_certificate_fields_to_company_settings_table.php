<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_settings', function (Blueprint $table): void {
            $table->boolean('learning_notify_on_assignment')->default(true)->after('mail_reply_to_address');
            $table->boolean('learning_notify_on_reminder')->default(true)->after('learning_notify_on_assignment');
            $table->boolean('learning_notify_on_completion')->default(true)->after('learning_notify_on_reminder');

            $table->string('learning_assignment_subject')->nullable()->after('learning_notify_on_completion');
            $table->text('learning_assignment_body')->nullable()->after('learning_assignment_subject');
            $table->string('learning_reminder_subject')->nullable()->after('learning_assignment_body');
            $table->text('learning_reminder_body')->nullable()->after('learning_reminder_subject');
            $table->string('learning_completion_subject')->nullable()->after('learning_reminder_body');
            $table->text('learning_completion_body')->nullable()->after('learning_completion_subject');

            $table->string('learning_certificate_title')->nullable()->after('learning_completion_body');
            $table->string('learning_certificate_subtitle')->nullable()->after('learning_certificate_title');
            $table->string('learning_certificate_signatory_name')->nullable()->after('learning_certificate_subtitle');
            $table->string('learning_certificate_signatory_title')->nullable()->after('learning_certificate_signatory_name');
            $table->text('learning_certificate_footer_note')->nullable()->after('learning_certificate_signatory_title');
        });
    }

    public function down(): void
    {
        Schema::table('company_settings', function (Blueprint $table): void {
            $table->dropColumn([
                'learning_notify_on_assignment',
                'learning_notify_on_reminder',
                'learning_notify_on_completion',
                'learning_assignment_subject',
                'learning_assignment_body',
                'learning_reminder_subject',
                'learning_reminder_body',
                'learning_completion_subject',
                'learning_completion_body',
                'learning_certificate_title',
                'learning_certificate_subtitle',
                'learning_certificate_signatory_name',
                'learning_certificate_signatory_title',
                'learning_certificate_footer_note',
            ]);
        });
    }
};

