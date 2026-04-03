<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LearningProgressEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'learning_enrollment_id',
        'learning_asset_id',
        'event_type',
        'progress_percent',
        'current_page',
        'total_pages',
        'position_seconds',
        'duration_seconds',
        'meta',
        'created_by_user_id',
    ];

    protected $casts = [
        'progress_percent' => 'decimal:2',
        'meta' => 'array',
    ];

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(LearningEnrollment::class, 'learning_enrollment_id');
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(LearningAsset::class, 'learning_asset_id');
    }
}

