<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmployeeSignatureRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_onboarding_id',
        'user_id',
        'document_type',
        'document_path',
        'status',
        'signer_name',
        'signer_email',
        'token',
        'expires_at',
        'signed_at',
        'signed_acknowledgement',
        'signed_hash',
        'signed_document_path',
        'initiated_by_user_id',
        'completed_by_user_id',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'signed_at' => 'datetime',
    ];

    public function onboarding(): BelongsTo
    {
        return $this->belongsTo(EmployeeOnboarding::class, 'employee_onboarding_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function events(): HasMany
    {
        return $this->hasMany(EmployeeSignatureEvent::class, 'employee_signature_request_id');
    }

    public function signers(): HasMany
    {
        return $this->hasMany(EmployeeSignatureSigner::class, 'employee_signature_request_id')
            ->orderBy('sign_order')
            ->orderBy('id');
    }
}
