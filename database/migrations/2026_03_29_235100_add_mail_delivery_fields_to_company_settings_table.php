<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_settings', function (Blueprint $table) {
            $table->string('mail_mailer', 30)->default('log')->after('cloudinary_secure_delivery');
            $table->boolean('ses_enabled')->default(false)->after('mail_mailer');
            $table->string('ses_region', 100)->nullable()->after('ses_enabled');
            $table->string('ses_access_key_id')->nullable()->after('ses_region');
            $table->string('ses_secret_access_key')->nullable()->after('ses_access_key_id');
            $table->string('ses_configuration_set')->nullable()->after('ses_secret_access_key');
            $table->string('mail_from_address')->nullable()->after('ses_configuration_set');
            $table->string('mail_from_name')->nullable()->after('mail_from_address');
            $table->string('mail_reply_to_address')->nullable()->after('mail_from_name');
        });
    }

    public function down(): void
    {
        Schema::table('company_settings', function (Blueprint $table) {
            $table->dropColumn([
                'mail_mailer',
                'ses_enabled',
                'ses_region',
                'ses_access_key_id',
                'ses_secret_access_key',
                'ses_configuration_set',
                'mail_from_address',
                'mail_from_name',
                'mail_reply_to_address',
            ]);
        });
    }
};

