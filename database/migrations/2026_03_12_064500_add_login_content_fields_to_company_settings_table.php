<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_settings', function (Blueprint $table) {
            $table->string('login_page_title')->nullable()->after('sidebar_muted_text_color');
            $table->string('login_brand_label')->nullable()->after('login_page_title');
            $table->string('login_hero_title')->nullable()->after('login_brand_label');
            $table->text('login_hero_copy')->nullable()->after('login_hero_title');
            $table->string('login_left_label')->nullable()->after('login_hero_copy');
            $table->text('login_left_copy')->nullable()->after('login_left_label');
            $table->string('login_right_label')->nullable()->after('login_left_copy');
            $table->text('login_right_copy')->nullable()->after('login_right_label');
            $table->string('login_help_line_one')->nullable()->after('login_right_copy');
            $table->string('login_help_line_two')->nullable()->after('login_help_line_one');
            $table->string('login_help_line_three')->nullable()->after('login_help_line_two');
        });
    }

    public function down(): void
    {
        Schema::table('company_settings', function (Blueprint $table) {
            $table->dropColumn([
                'login_page_title',
                'login_brand_label',
                'login_hero_title',
                'login_hero_copy',
                'login_left_label',
                'login_left_copy',
                'login_right_label',
                'login_right_copy',
                'login_help_line_one',
                'login_help_line_two',
                'login_help_line_three',
            ]);
        });
    }
};
