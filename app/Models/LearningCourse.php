<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class LearningCourse extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_code',
        'title',
        'slug',
        'description',
        'delivery_mode',
        'visibility_status',
        'catalog_visible',
        'audience_scope',
        'target_departments',
        'target_roles',
        'target_locations',
        'status',
        'join_link',
        'venue',
        'start_at',
        'end_at',
        'estimated_duration_minutes',
        'created_by_user_id',
    ];

    protected $casts = [
        'catalog_visible' => 'boolean',
        'target_departments' => 'array',
        'target_roles' => 'array',
        'target_locations' => 'array',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $course): void {
            if (blank($course->slug) && filled($course->title)) {
                $base = Str::slug((string) $course->title);
                $course->slug = $base ?: Str::random(10);
            }

            if (($course->audience_scope ?? 'all') !== 'filtered') {
                $course->target_departments = null;
                $course->target_roles = null;
                $course->target_locations = null;
            }
        });
    }

    public function assets(): HasMany
    {
        return $this->hasMany(LearningAsset::class)->orderBy('sort_order');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(LearningEnrollment::class);
    }
}
