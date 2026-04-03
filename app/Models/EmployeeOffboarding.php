<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeOffboarding extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'offboarding_status',
        'offboarding_type',
        'notice_submitted_on',
        'last_working_day',
        'exit_interview_date',
        'exit_interview_completed',
        'knowledge_transfer_completed',
        'assets_returned',
        'access_revoked',
        'final_settlement_completed',
        'rehire_eligible',
        'offboarding_reason',
        'offboarding_notes',
        'initiated_by_user_id',
        'completed_by_user_id',
        'completed_at',
    ];

    protected $casts = [
        'notice_submitted_on' => 'date',
        'last_working_day' => 'date',
        'exit_interview_date' => 'date',
        'exit_interview_completed' => 'boolean',
        'knowledge_transfer_completed' => 'boolean',
        'assets_returned' => 'boolean',
        'access_revoked' => 'boolean',
        'final_settlement_completed' => 'boolean',
        'rehire_eligible' => 'boolean',
        'completed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
