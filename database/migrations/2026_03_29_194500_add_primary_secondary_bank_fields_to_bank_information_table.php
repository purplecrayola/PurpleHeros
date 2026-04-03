<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bank_information', function (Blueprint $table) {
            $table->string('primary_bank_name')->nullable()->after('user_id');
            $table->string('primary_bank_account_no')->nullable()->after('primary_bank_name');
            $table->string('primary_ifsc_code')->nullable()->after('primary_bank_account_no');
            $table->string('primary_pan_no')->nullable()->after('primary_ifsc_code');

            $table->string('secondary_bank_name')->nullable()->after('primary_pan_no');
            $table->string('secondary_bank_account_no')->nullable()->after('secondary_bank_name');
            $table->string('secondary_ifsc_code')->nullable()->after('secondary_bank_account_no');
            $table->string('secondary_pan_no')->nullable()->after('secondary_ifsc_code');
        });
    }

    public function down(): void
    {
        Schema::table('bank_information', function (Blueprint $table) {
            $table->dropColumn([
                'primary_bank_name',
                'primary_bank_account_no',
                'primary_ifsc_code',
                'primary_pan_no',
                'secondary_bank_name',
                'secondary_bank_account_no',
                'secondary_ifsc_code',
                'secondary_pan_no',
            ]);
        });
    }
};

