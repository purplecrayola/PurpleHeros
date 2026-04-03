<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $this->backfillTable('users');
        $this->backfillTable('employees');
        $this->backfillTable('profile_information');
    }

    public function down(): void
    {
        // Intentionally left empty: this migration only backfills derived data.
    }

    private function backfillTable(string $table): void
    {
        DB::table($table)
            ->select(['id', 'name', 'first_name', 'last_name'])
            ->orderBy('id')
            ->chunkById(200, function ($rows) use ($table): void {
                foreach ($rows as $row) {
                    $name = trim((string) ($row->name ?? ''));
                    if ($name === '') {
                        continue;
                    }

                    $currentFirst = trim((string) ($row->first_name ?? ''));
                    $currentLast = trim((string) ($row->last_name ?? ''));
                    if ($currentFirst !== '' && $currentLast !== '') {
                        continue;
                    }

                    $parts = preg_split('/\s+/', $name) ?: [];
                    $first = $currentFirst !== '' ? $currentFirst : ($parts[0] ?? null);
                    $last = $currentLast !== ''
                        ? $currentLast
                        : (count($parts) > 1 ? implode(' ', array_slice($parts, 1)) : null);

                    DB::table($table)
                        ->where('id', $row->id)
                        ->update([
                            'first_name' => $first,
                            'last_name' => $last,
                        ]);
                }
            });
    }
};
