<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_references', function (Blueprint $table) {
            $table->string('request_status', 20)->default('not_sent')->after('years_known');
            $table->string('request_token', 120)->nullable()->unique()->after('request_status');
            $table->timestamp('requested_at')->nullable()->after('request_token');
            $table->timestamp('request_expires_at')->nullable()->after('requested_at');
            $table->timestamp('responded_at')->nullable()->after('request_expires_at');
            $table->json('response_payload')->nullable()->after('responded_at');
            $table->tinyInteger('response_rating')->nullable()->after('response_payload');
        });
    }

    public function down(): void
    {
        Schema::table('employee_references', function (Blueprint $table) {
            $table->dropUnique('employee_references_request_token_unique');
            $table->dropColumn([
                'request_status',
                'request_token',
                'requested_at',
                'request_expires_at',
                'responded_at',
                'response_payload',
                'response_rating',
            ]);
        });
    }
};
