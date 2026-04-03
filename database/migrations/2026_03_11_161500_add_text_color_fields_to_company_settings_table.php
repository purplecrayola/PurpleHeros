<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_settings', function (Blueprint $table) {
            $table->string('header_text_color', 7)->nullable()->after('brand_neutral_color');
            $table->string('sidebar_text_color', 7)->nullable()->after('header_text_color');
            $table->string('sidebar_muted_text_color', 7)->nullable()->after('sidebar_text_color');
        });
    }

    public function down(): void
    {
        Schema::table('company_settings', function (Blueprint $table) {
            $table->dropColumn([
                'header_text_color',
                'sidebar_text_color',
                'sidebar_muted_text_color',
            ]);
        });
    }
};
