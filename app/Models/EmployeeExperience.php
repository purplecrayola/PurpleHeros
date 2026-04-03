<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeExperience extends Model
{
    use HasFactory;

    protected $table = 'employee_experiences';

    protected $fillable = [
        'user_id',
        'company_name',
        'job_title',
        'location',
        'start_date',
        'end_date',
        'is_current',
        'summary',
        'created_by_user_id',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_current' => 'boolean',
    ];
}
