<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class TimesheetEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'work_date',
        'project_name',
        'assigned_hours',
        'worked_hours',
        'description',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $entry): void {
            $assigned = (int) $entry->assigned_hours;
            $worked = (int) $entry->worked_hours;
            $maxAllowedWorkedHours = min($assigned + 4, 24);

            if ($worked > $maxAllowedWorkedHours) {
                throw ValidationException::withMessages([
                    'worked_hours' => "Worked hours cannot exceed assigned hours by more than 4 (max {$maxAllowedWorkedHours} for this entry).",
                ]);
            }
        });
    }
}
