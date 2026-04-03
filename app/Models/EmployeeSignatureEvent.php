<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeSignatureEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_signature_request_id',
        'event_type',
        'event_payload',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'event_payload' => 'array',
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(EmployeeSignatureRequest::class, 'employee_signature_request_id');
    }
}

