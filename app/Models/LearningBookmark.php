<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LearningBookmark extends Model
{
    use HasFactory;

    protected $fillable = [
        'learning_enrollment_id',
        'learning_asset_id',
        'page_number',
        'position_seconds',
        'label',
        'note',
        'created_by_user_id',
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

