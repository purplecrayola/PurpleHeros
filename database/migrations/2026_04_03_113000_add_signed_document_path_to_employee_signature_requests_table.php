<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_signature_requests', function (Blueprint $table) {
            $table->string('signed_document_path')->nullable()->after('signed_hash');
        });
    }

    public function down(): void
    {
        Schema::table('employee_signature_requests', function (Blueprint $table) {
            $table->dropColumn('signed_document_path');
        });
    }
};
