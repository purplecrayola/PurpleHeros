<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class EmployeeOnboarding extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'onboarding_status',
        'offer_status',
        'offer_document_path',
        'offer_signers_json',
        'offer_sign_provider',
        'offer_signature_request_id',
        'offer_sent_at',
        'offer_signed_at',
        'contract_status',
        'contract_document_path',
        'contract_signers_json',
        'contract_sign_provider',
        'contract_signature_request_id',
        'contract_sent_at',
        'contract_signed_at',
        'reference_check_status',
        'references_total_count',
        'references_verified_count',
        'background_check_status',
        'planned_start_date',
        'onboarding_completed_at',
        'onboarding_notes',
        'created_by_user_id',
        'updated_by_user_id',
    ];

    protected $casts = [
        'offer_sent_at' => 'date',
        'offer_signed_at' => 'date',
        'offer_signers_json' => 'array',
        'contract_sent_at' => 'date',
        'contract_signed_at' => 'date',
        'contract_signers_json' => 'array',
        'planned_start_date' => 'date',
        'onboarding_completed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function signatureRequests(): HasMany
    {
        return $this->hasMany(EmployeeSignatureRequest::class, 'employee_onboarding_id');
    }

    public function references(): HasMany
    {
        return $this->hasMany(EmployeeReference::class, 'user_id', 'user_id');
    }

    public function latestOfferSignatureRequest(): HasOne
    {
        return $this->hasOne(EmployeeSignatureRequest::class, 'employee_onboarding_id')
            ->where('document_type', 'offer')
            ->latestOfMany('id');
    }

    public function latestContractSignatureRequest(): HasOne
    {
        return $this->hasOne(EmployeeSignatureRequest::class, 'employee_onboarding_id')
            ->where('document_type', 'contract')
            ->latestOfMany('id');
    }
}
