<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'payroll_run_id',
        'payroll_run_employee_id',
        'user_id',
        'employee_name',
        'provider',
        'account_source',
        'payment_type',
        'payment_note',
        'bank_name',
        'account_number',
        'account_name',
        'amount',
        'currency',
        'status',
        'provider_reference',
        'idempotency_key',
        'failure_reason',
        'request_payload',
        'provider_response',
        'requested_by_user_id',
        'processed_at',
    ];

    protected $casts = [
        'request_payload' => 'array',
        'provider_response' => 'array',
        'processed_at' => 'datetime',
    ];

    public function payrollRun(): BelongsTo
    {
        return $this->belongsTo(PayrollRun::class);
    }

    public function payrollRunEmployee(): BelongsTo
    {
        return $this->belongsTo(PayrollRunEmployee::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
