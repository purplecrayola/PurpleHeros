<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leaves_admins', function (Blueprint $table) {
            $table->string('leave_signature', 64)->nullable()->after('leave_reason');
        });

        $used = [];
        DB::table('leaves_admins')
            ->orderBy('id')
            ->select('id', 'user_id', 'leave_type', 'from_date', 'to_date')
            ->cursor()
            ->each(function ($row) use (&$used): void {
                $base = hash('sha256', implode('|', [
                    strtolower(trim((string) $row->user_id)),
                    strtolower(trim((string) $row->leave_type)),
                    trim((string) $row->from_date),
                    trim((string) $row->to_date),
                ]));

                $signature = $base;
                if (isset($used[$signature])) {
                    $signature = hash('sha256', $base . '|' . $row->id);
                }

                $used[$signature] = true;

                DB::table('leaves_admins')
                    ->where('id', $row->id)
                    ->update(['leave_signature' => $signature]);
            });

        Schema::table('leaves_admins', function (Blueprint $table) {
            $table->unique('leave_signature', 'leaves_admins_leave_signature_unique');
        });
    }

    public function down(): void
    {
        Schema::table('leaves_admins', function (Blueprint $table) {
            $table->dropUnique('leaves_admins_leave_signature_unique');
            $table->dropColumn('leave_signature');
        });
    }
};
