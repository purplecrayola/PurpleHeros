<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerformanceObjective extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'period_type',
        'period_start',
        'period_end',
        'weight',
        'source',
        'created_by_user_id',
        'status',
    ];
}
