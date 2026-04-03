<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_signature_signers', function (Blueprint $table) {
            $table->decimal('position_x', 8, 2)->nullable()->after('page_number');
            $table->decimal('position_y', 8, 2)->nullable()->after('position_x');
            $table->decimal('field_width', 8, 2)->nullable()->after('position_y');
            $table->decimal('field_height', 8, 2)->nullable()->after('field_width');
        });
    }

    public function down(): void
    {
        Schema::table('employee_signature_signers', function (Blueprint $table) {
            $table->dropColumn(['position_x', 'position_y', 'field_width', 'field_height']);
        });
    }
};
