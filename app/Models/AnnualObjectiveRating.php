<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnnualObjectiveRating extends Model
{
    use HasFactory;

    protected $fillable = [
        'annual_performance_review_id',
        'performance_objective_id',
        'self_rating',
        'self_comment',
        'manager_rating',
        'manager_comment',
    ];

    public function objective()
    {
        return $this->belongsTo(PerformanceObjective::class, 'performance_objective_id');
    }
}
