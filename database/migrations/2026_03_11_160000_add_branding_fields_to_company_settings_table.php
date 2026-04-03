<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_settings', function (Blueprint $table) {
            $table->string('header_logo_path')->nullable()->after('website_url');
            $table->string('login_logo_path')->nullable()->after('header_logo_path');
            $table->string('login_image_path')->nullable()->after('login_logo_path');
            $table->string('brand_primary_color', 7)->nullable()->after('login_image_path');
            $table->string('brand_dark_color', 7)->nullable()->after('brand_primary_color');
            $table->string('brand_neutral_color', 7)->nullable()->after('brand_dark_color');
        });
    }

    public function down(): void
    {
        Schema::table('company_settings', function (Blueprint $table) {
            $table->dropColumn([
                'header_logo_path',
                'login_logo_path',
                'login_image_path',
                'brand_primary_color',
                'brand_dark_color',
                'brand_neutral_color',
            ]);
        });
    }
};
