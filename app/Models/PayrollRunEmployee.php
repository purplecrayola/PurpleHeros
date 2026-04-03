<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayrollRunEmployee extends Model
{
    use HasFactory;

    protected $fillable = [
        'payroll_run_id',
        'user_id',
        'employee_name',
        'designation',
        'department',
        'tax_station',
        'account_number',
        'source',
        'status',
        'input_payload',
        'computed_payload',
        'gross_salary',
        'total_taxable_earnings',
        'total_deductions',
        'net_salary',
        'total_paid',
    ];

    protected $casts = [
        'input_payload' => 'array',
        'computed_payload' => 'array',
    ];

    public function payrollRun(): BelongsTo
    {
        return $this->belongsTo(PayrollRun::class);
    }

    public function lineItems(): HasMany
    {
        return $this->hasMany(PayrollLineItem::class);
    }

    public function payslips(): HasMany
    {
        return $this->hasMany(Payslip::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(PayrollPayment::class);
    }
}
