<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerformanceGoalEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'period_type',
        'period_year',
        'period_number',
        'title',
        'planned_tasks',
        'end_period_update',
        'completion_percent',
        'blockers',
        'manager_comment',
        'status',
        'submitted_at',
        'manager_reviewed_at',
        'created_by_user_id',
    ];
}
