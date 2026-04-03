<?php

namespace App\Models;

use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OvertimeEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'ot_date',
        'hours',
        'ot_type',
        'status',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'description',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public function approveBy(User $approver): void
    {
        $this->update([
            'status' => 'Approved',
            'approved_by' => $approver->email,
            'approved_at' => Carbon::now(),
            'rejection_reason' => null,
        ]);
    }

    public function rejectBy(User $approver, ?string $reason = null): void
    {
        $this->update([
            'status' => 'Rejected',
            'approved_by' => $approver->email,
            'approved_at' => Carbon::now(),
            'rejection_reason' => $reason,
        ]);
    }
}
