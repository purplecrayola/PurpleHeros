<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_settings', function (Blueprint $table) {
            $table->string('storage_provider')->default('local')->after('allow_employee_bank_edit');
            $table->string('cloudinary_cloud_name')->nullable()->after('storage_provider');
            $table->string('cloudinary_api_key')->nullable()->after('cloudinary_cloud_name');
            $table->string('cloudinary_api_secret')->nullable()->after('cloudinary_api_key');
            $table->string('cloudinary_folder')->nullable()->after('cloudinary_api_secret');
            $table->boolean('cloudinary_secure_delivery')->default(true)->after('cloudinary_folder');
        });
    }

    public function down(): void
    {
        Schema::table('company_settings', function (Blueprint $table) {
            $table->dropColumn([
                'storage_provider',
                'cloudinary_cloud_name',
                'cloudinary_api_key',
                'cloudinary_api_secret',
                'cloudinary_folder',
                'cloudinary_secure_delivery',
            ]);
        });
    }
};

