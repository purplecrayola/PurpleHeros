<?php

namespace App\Models;

use App\Support\MediaStorageManager;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payslip extends Model
{
    use HasFactory;

    protected $fillable = [
        'payroll_run_id',
        'payroll_run_employee_id',
        'user_id',
        'period_year',
        'period_month',
        'source',
        'file_path',
        'payload',
        'issued_at',
        'published_at',
        'is_locked',
    ];

    protected $casts = [
        'payload' => 'array',
        'issued_at' => 'datetime',
        'published_at' => 'datetime',
        'is_locked' => 'boolean',
    ];

    protected $appends = [
        'file_url',
    ];

    public function payrollRun(): BelongsTo
    {
        return $this->belongsTo(PayrollRun::class);
    }

    public function payrollRunEmployee(): BelongsTo
    {
        return $this->belongsTo(PayrollRunEmployee::class);
    }

    public function getFileUrlAttribute(): string
    {
        return MediaStorageManager::publicUrl($this->file_path);
    }
}
