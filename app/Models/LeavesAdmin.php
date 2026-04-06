<?php

namespace App\Models;

use App\Support\InAppNotifier;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class LeavesAdmin extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'leave_type',
        'from_date',
        'to_date',
        'day',
        'status',
        'approved_by',
        'approved_at',
        'rejection_reason',
        'leave_reason',
        'leave_signature',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $leave): void {
            $leave->from_date = self::normalizeDate($leave->from_date);
            $leave->to_date = self::normalizeDate($leave->to_date);

            if (! $leave->status) {
                $leave->status = 'Pending';
            }

            if (self::hasOverlappingRange(
                (string) $leave->user_id,
                (string) $leave->from_date,
                (string) $leave->to_date,
                $leave->exists ? (int) $leave->id : null
            )) {
                throw ValidationException::withMessages([
                    'from_date' => 'Leave dates overlap with an existing request for this user.',
                ]);
            }

            $leave->leave_signature = self::buildSignature(
                (string) $leave->user_id,
                (string) $leave->leave_type,
                (string) $leave->from_date,
                (string) $leave->to_date
            );
        });
    }

    public function approveBy(User $approver): void
    {
        $this->update([
            'status' => 'Approved',
            'approved_by' => $approver->email,
            'approved_at' => Carbon::now(),
            'rejection_reason' => null,
        ]);

        InAppNotifier::notifyUserId(
            (string) $this->user_id,
            'Leave request approved',
            sprintf('Your %s leave request has been approved.', (string) $this->leave_type),
            route('form/leavesemployee/new'),
            'success'
        );
    }

    public function rejectBy(User $approver, ?string $reason = null): void
    {
        $this->update([
            'status' => 'Rejected',
            'approved_by' => $approver->email,
            'approved_at' => Carbon::now(),
            'rejection_reason' => $reason,
        ]);

        $message = sprintf('Your %s leave request was rejected.', (string) $this->leave_type);
        if ($reason) {
            $message .= ' Reason: ' . $reason;
        }

        InAppNotifier::notifyUserId(
            (string) $this->user_id,
            'Leave request rejected',
            $message,
            route('form/leavesemployee/new'),
            'negative'
        );
    }

    public static function buildSignature(string $userId, string $leaveType, string $fromDate, string $toDate): string
    {
        return hash('sha256', implode('|', [
            strtolower(trim($userId)),
            strtolower(trim($leaveType)),
            trim(self::normalizeDate($fromDate)),
            trim(self::normalizeDate($toDate)),
        ]));
    }

    public static function hasOverlappingRange(string $userId, string $fromDate, string $toDate, ?int $ignoreId = null): bool
    {
        $from = self::normalizeDate($fromDate);
        $to = self::normalizeDate($toDate);

        return self::query()
            ->where('user_id', $userId)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->whereDate('from_date', '<=', $to)
            ->whereDate('to_date', '>=', $from)
            ->exists();
    }

    private static function normalizeDate(string $value): string
    {
        try {
            return Carbon::parse($value)->toDateString();
        } catch (\Throwable $exception) {
            return trim($value);
        }
    }
}
