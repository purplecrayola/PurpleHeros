<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_onboardings', function (Blueprint $table) {
            $table->json('offer_signers_json')->nullable()->after('offer_document_path');
            $table->json('contract_signers_json')->nullable()->after('contract_document_path');
        });
    }

    public function down(): void
    {
        Schema::table('employee_onboardings', function (Blueprint $table) {
            $table->dropColumn(['offer_signers_json', 'contract_signers_json']);
        });
    }
};
