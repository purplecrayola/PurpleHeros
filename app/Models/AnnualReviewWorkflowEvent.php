<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnnualReviewWorkflowEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'annual_performance_review_id',
        'from_step',
        'to_step',
        'action',
        'actor_user_id',
        'actor_role',
        'notes',
        'notified_emails',
    ];

    protected $casts = [
        'notified_emails' => 'array',
    ];

    public function review()
    {
        return $this->belongsTo(AnnualPerformanceReview::class, 'annual_performance_review_id');
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_user_id', 'user_id');
    }
}

