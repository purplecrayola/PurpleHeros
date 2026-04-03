<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_settings', function (Blueprint $table) {
            $table->string('locale')->nullable()->after('website_url');
            $table->string('timezone')->nullable()->after('locale');
            $table->string('currency_code', 10)->nullable()->after('timezone');
            $table->string('currency_symbol', 10)->nullable()->after('currency_code');
            $table->string('date_format', 30)->nullable()->after('currency_symbol');
            $table->string('time_format', 30)->nullable()->after('date_format');
            $table->string('week_starts_on', 20)->nullable()->after('time_format');
        });
    }

    public function down(): void
    {
        Schema::table('company_settings', function (Blueprint $table) {
            $table->dropColumn([
                'locale',
                'timezone',
                'currency_code',
                'currency_symbol',
                'date_format',
                'time_format',
                'week_starts_on',
            ]);
        });
    }
};
