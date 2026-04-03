<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LearningAsset extends Model
{
    use HasFactory;

    protected $fillable = [
        'learning_course_id',
        'asset_type',
        'title',
        'description',
        'file_path',
        'external_url',
        'sort_order',
        'is_required',
        'duration_seconds',
        'pages_count',
        'status',
    ];

    protected $casts = [
        'is_required' => 'boolean',
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(LearningCourse::class, 'learning_course_id');
    }

    public function progressEvents(): HasMany
    {
        return $this->hasMany(LearningProgressEvent::class, 'learning_asset_id');
    }
}

