<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeSignatureSigner extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_signature_request_id',
        'role_label',
        'signer_name',
        'signer_email',
        'sign_order',
        'signature_field_key',
        'page_number',
        'position_x',
        'position_y',
        'field_width',
        'field_height',
        'status',
        'token',
        'signed_at',
        'signed_acknowledgement',
        'signed_hash',
        'completed_by_user_id',
    ];

    protected $casts = [
        'signed_at' => 'datetime',
        'position_x' => 'float',
        'position_y' => 'float',
        'field_width' => 'float',
        'field_height' => 'float',
    ];

    public function signatureRequest(): BelongsTo
    {
        return $this->belongsTo(EmployeeSignatureRequest::class, 'employee_signature_request_id');
    }
}
