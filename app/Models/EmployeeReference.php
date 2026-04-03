<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeReference extends Model
{
    use HasFactory;

    protected $table = 'employee_references';

    protected $fillable = [
        'user_id',
        'referee_name',
        'relationship',
        'company_name',
        'job_title',
        'email',
        'phone',
        'years_known',
        'request_status',
        'request_token',
        'requested_at',
        'request_expires_at',
        'responded_at',
        'response_payload',
        'response_rating',
        'is_verified',
        'verification_feedback',
        'verified_by_user_id',
        'verified_at',
        'created_by_user_id',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'requested_at' => 'datetime',
        'request_expires_at' => 'datetime',
        'responded_at' => 'datetime',
        'response_payload' => 'array',
        'response_rating' => 'integer',
        'verified_at' => 'datetime',
    ];

    public function employeeUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
