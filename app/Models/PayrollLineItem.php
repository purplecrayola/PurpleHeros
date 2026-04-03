<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollLineItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'payroll_run_employee_id',
        'line_type',
        'code',
        'label',
        'is_taxable',
        'amount',
        'sort_order',
        'meta',
    ];

    protected $casts = [
        'is_taxable' => 'boolean',
        'meta' => 'array',
    ];

    public function payrollRunEmployee(): BelongsTo
    {
        return $this->belongsTo(PayrollRunEmployee::class);
    }
}
