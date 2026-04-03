<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayslipImportRow extends Model
{
    use HasFactory;

    protected $fillable = [
        'payslip_import_batch_id',
        'payroll_run_employee_id',
        'user_id',
        'employee_name',
        'period_year',
        'period_month',
        'gross_salary',
        'total_deductions',
        'net_salary',
        'file_path',
        'payload',
        'error_message',
        'row_status',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    public function batch(): BelongsTo
    {
        return $this->belongsTo(PayslipImportBatch::class, 'payslip_import_batch_id');
    }

    public function payrollRunEmployee(): BelongsTo
    {
        return $this->belongsTo(PayrollRunEmployee::class);
    }
}
