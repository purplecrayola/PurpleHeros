<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayrollPolicySet extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'country_code',
        'state_code',
        'currency_code',
        'is_active',
        'effective_from',
        'effective_to',
        'settings',
        'created_by_user_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'effective_from' => 'date',
        'effective_to' => 'date',
        'settings' => 'array',
    ];

    public function payrollRuns(): HasMany
    {
        return $this->hasMany(PayrollRun::class);
    }

    public static function active(): ?self
    {
        return self::query()->where('is_active', true)->orderByDesc('effective_from')->first();
    }

    public function resolvedSettings(): array
    {
        $default = [
            'tax_free_threshold' => 800000,
            'rent_relief_percent' => 20,
            'rent_relief_cap' => 500000,
            'default_pension_enabled' => true,
            'default_employee_pension_rate_percent' => 8,
            'default_nhf_enabled' => false,
            'default_nhf_rate_percent' => 2.5,
            'paye_bands' => [
                ['up_to' => 2200000, 'rate' => 15],
                ['up_to' => 11200000, 'rate' => 18],
                ['up_to' => 24200000, 'rate' => 21],
                ['up_to' => 49200000, 'rate' => 23],
                ['up_to' => null, 'rate' => 25],
            ],
        ];

        return array_replace_recursive($default, $this->settings ?? []);
    }
}
