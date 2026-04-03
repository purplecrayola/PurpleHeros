<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_settings', function (Blueprint $table) {
            $table->json('employee_field_rules_json')->nullable()->after('allow_employee_bank_edit');
            $table->json('employee_status_options_json')->nullable()->after('employee_field_rules_json');
        });
    }

    public function down(): void
    {
        Schema::table('company_settings', function (Blueprint $table) {
            $table->dropColumn([
                'employee_field_rules_json',
                'employee_status_options_json',
            ]);
        });
    }
};

