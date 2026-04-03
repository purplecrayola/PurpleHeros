<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnnualPerformanceReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'review_year',
        'self_summary',
        'manager_summary',
        'self_objectives_score',
        'self_values_score',
        'manager_objectives_score',
        'manager_values_score',
        'manager_final_score',
        'self_submitted_at',
        'manager_submitted_at',
        'calibration_completed_at',
        'joint_review_at',
        'employee_acknowledged_at',
        'finalized_at',
        'status',
        'workflow_step',
        'workflow_notes',
        'manager_user_id',
        'finalized_by_user_id',
    ];

    protected $casts = [
        'self_submitted_at' => 'datetime',
        'manager_submitted_at' => 'datetime',
        'calibration_completed_at' => 'datetime',
        'joint_review_at' => 'datetime',
        'employee_acknowledged_at' => 'datetime',
        'finalized_at' => 'datetime',
    ];

    public function objectiveRatings()
    {
        return $this->hasMany(AnnualObjectiveRating::class, 'annual_performance_review_id');
    }

    public function valueRatings()
    {
        return $this->hasMany(AnnualValueRating::class, 'annual_performance_review_id');
    }

    public function workflowEvents()
    {
        return $this->hasMany(AnnualReviewWorkflowEvent::class, 'annual_performance_review_id');
    }
}
