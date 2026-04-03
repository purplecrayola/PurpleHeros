<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnnualValueRating extends Model
{
    use HasFactory;

    protected $fillable = [
        'annual_performance_review_id',
        'value_key',
        'value_label',
        'self_rating',
        'self_comment',
        'manager_rating',
        'manager_comment',
    ];
}
