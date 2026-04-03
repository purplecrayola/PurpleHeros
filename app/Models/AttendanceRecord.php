<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'attendance_date',
        'status',
        'check_in',
        'check_out',
        'work_minutes',
        'break_minutes',
        'overtime_minutes',
        'notes',
    ];
}
