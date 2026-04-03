<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_settings', function (Blueprint $table) {
            $table->boolean('opay_enabled')->default(false)->after('mail_reply_to_address');
            $table->boolean('opay_sandbox_mode')->default(true)->after('opay_enabled');
            $table->string('opay_base_url')->nullable()->after('opay_sandbox_mode');
            $table->string('opay_transfer_path')->nullable()->after('opay_base_url');
            $table->string('opay_merchant_id')->nullable()->after('opay_transfer_path');
            $table->string('opay_public_key')->nullable()->after('opay_merchant_id');
            $table->string('opay_secret_key')->nullable()->after('opay_public_key');

            $table->boolean('kuda_enabled')->default(false)->after('opay_secret_key');
            $table->boolean('kuda_sandbox_mode')->default(true)->after('kuda_enabled');
            $table->string('kuda_base_url')->nullable()->after('kuda_sandbox_mode');
            $table->string('kuda_transfer_path')->nullable()->after('kuda_base_url');
            $table->string('kuda_api_key')->nullable()->after('kuda_transfer_path');
            $table->string('kuda_secret_key')->nullable()->after('kuda_api_key');
            $table->string('kuda_client_email')->nullable()->after('kuda_secret_key');
        });
    }

    public function down(): void
    {
        Schema::table('company_settings', function (Blueprint $table) {
            $table->dropColumn([
                'opay_enabled',
                'opay_sandbox_mode',
                'opay_base_url',
                'opay_transfer_path',
                'opay_merchant_id',
                'opay_public_key',
                'opay_secret_key',
                'kuda_enabled',
                'kuda_sandbox_mode',
                'kuda_base_url',
                'kuda_transfer_path',
                'kuda_api_key',
                'kuda_secret_key',
                'kuda_client_email',
            ]);
        });
    }
};

