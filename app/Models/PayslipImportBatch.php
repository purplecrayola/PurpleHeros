<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayslipImportBatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'payroll_run_id',
        'title',
        'period_year',
        'period_month',
        'source_file_name',
        'import_file_path',
        'uploaded_by_user_id',
        'status',
        'processed_rows',
        'failed_rows',
        'notes',
    ];

    public function payrollRun(): BelongsTo
    {
        return $this->belongsTo(PayrollRun::class);
    }

    public function rows(): HasMany
    {
        return $this->hasMany(PayslipImportRow::class);
    }
}
