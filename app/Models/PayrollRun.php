<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayrollRun extends Model
{
    use HasFactory;

    protected $fillable = [
        'payroll_policy_set_id',
        'run_code',
        'period_year',
        'period_month',
        'period_start',
        'period_end',
        'default_worked_days',
        'default_total_working_days',
        'status',
        'calculated_at',
        'approved_at',
        'posted_at',
        'locked_at',
        'created_by_user_id',
        'notes',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'calculated_at' => 'datetime',
        'approved_at' => 'datetime',
        'posted_at' => 'datetime',
        'locked_at' => 'datetime',
    ];

    public function policySet(): BelongsTo
    {
        return $this->belongsTo(PayrollPolicySet::class, 'payroll_policy_set_id');
    }

    public function employees(): HasMany
    {
        return $this->hasMany(PayrollRunEmployee::class);
    }

    public function payslips(): HasMany
    {
        return $this->hasMany(Payslip::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(PayrollPayment::class);
    }

    public function getPeriodLabelAttribute(): string
    {
        return sprintf('%s %d', date('F', mktime(0, 0, 0, (int) $this->period_month, 1)), (int) $this->period_year);
    }
}
