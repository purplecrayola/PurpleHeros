<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeStatutoryProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'tax_station',
        'tax_residency_state',
        'salary_basis',
        'payment_type',
        'pension_enabled',
        'employee_pension_rate_percent',
        'employer_pension_rate_percent',
        'pension_pin',
        'nhf_enabled',
        'nhf_rate_percent',
        'nhf_base_cap',
        'nhf_number',
        'annual_rent',
        'other_statutory_deductions',
        'default_non_taxable_reimbursement',
        'pf_enabled',
        'pf_number',
        'pf_contribution_rate_percent',
        'pf_additional_rate_percent',
        'esi_enabled',
        'esi_number',
        'esi_contribution_rate_percent',
        'esi_additional_rate_percent',
        'created_by_user_id',
    ];

    protected $casts = [
        'pension_enabled' => 'boolean',
        'nhf_enabled' => 'boolean',
        'pf_enabled' => 'boolean',
        'esi_enabled' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
