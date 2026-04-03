<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('performance_review_settings', function (Blueprint $table) {
            $table->text('values_catalog_json')->nullable()->after('annual_section_values_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('performance_review_settings', function (Blueprint $table) {
            $table->dropColumn('values_catalog_json');
        });
    }
};
