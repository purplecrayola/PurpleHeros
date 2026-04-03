<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LearningEnrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'learning_course_id',
        'user_id',
        'assigned_by_user_id',
        'assigned_at',
        'due_at',
        'started_at',
        'last_activity_at',
        'completed_at',
        'status',
        'completion_percent',
        'is_mandatory',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'due_at' => 'datetime',
        'started_at' => 'datetime',
        'last_activity_at' => 'datetime',
        'completed_at' => 'datetime',
        'is_mandatory' => 'boolean',
        'completion_percent' => 'decimal:2',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(LearningCourse::class, 'learning_course_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by_user_id', 'user_id');
    }

    public function progressEvents(): HasMany
    {
        return $this->hasMany(LearningProgressEvent::class, 'learning_enrollment_id');
    }

    public function bookmarks(): HasMany
    {
        return $this->hasMany(LearningBookmark::class, 'learning_enrollment_id');
    }
}

