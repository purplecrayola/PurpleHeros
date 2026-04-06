<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_settings', function (Blueprint $table): void {
            if (! Schema::hasColumn('company_settings', 'people_ops_email')) {
                $table->string('people_ops_email')->nullable()->after('mail_reply_to_address');
            }
        });
    }

    public function down(): void
    {
        Schema::table('company_settings', function (Blueprint $table): void {
            if (Schema::hasColumn('company_settings', 'people_ops_email')) {
                $table->dropColumn('people_ops_email');
            }
        });
    }
};

