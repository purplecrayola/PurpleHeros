<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leaves_admins', function (Blueprint $table) {
            $table->string('status')->default('Pending')->after('day');
            $table->string('approved_by')->nullable()->after('status');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->string('rejection_reason')->nullable()->after('approved_at');
        });
    }

    public function down(): void
    {
        Schema::table('leaves_admins', function (Blueprint $table) {
            $table->dropColumn([
                'status',
                'approved_by',
                'approved_at',
                'rejection_reason',
            ]);
        });
    }
};
