<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payslip_import_batches', function (Blueprint $table) {
            $table->string('import_file_path')->nullable()->after('source_file_name');
        });
    }

    public function down(): void
    {
        Schema::table('payslip_import_batches', function (Blueprint $table) {
            $table->dropColumn('import_file_path');
        });
    }
};
