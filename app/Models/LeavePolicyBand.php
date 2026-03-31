<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeavePolicyBand extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'annual_entitlement_days',
        'carry_forward_enabled',
        'carry_forward_cap_days',
        'is_active',
        'sort_order',
        'description',
    ];

    protected $casts = [
        'annual_entitlement_days' => 'integer',
        'carry_forward_enabled' => 'boolean',
        'carry_forward_cap_days' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public static function activeOptions(): array
    {
        try {
            $options = static::query()
                ->active()
                ->orderBy('sort_order')
                ->orderBy('name')
                ->pluck('name', 'name')
                ->all();

            return $options !== [] ? $options : static::fallbackOptions();
        } catch (\Throwable $exception) {
            return static::fallbackOptions();
        }
    }

    public static function activeNameMap(): array
    {
        try {
            $bands = static::query()
                ->active()
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['name', 'category', 'annual_entitlement_days', 'carry_forward_enabled', 'carry_forward_cap_days'])
                ->keyBy(fn (self $band) => mb_strtolower(trim($band->name)))
                ->all();

            return $bands !== [] ? $bands : static::fallbackNameMap();
        } catch (\Throwable $exception) {
            return static::fallbackNameMap();
        }
    }

    public static function fallbackOptions(): array
    {
        return [
            'Annual Leave' => 'Annual Leave',
            'Sick Leave' => 'Sick Leave',
            'Maternity Leave' => 'Maternity Leave',
            'Unpaid Leave' => 'Unpaid Leave',
        ];
    }

    private static function fallbackNameMap(): array
    {
        $items = [
            ['name' => 'Annual Leave', 'category' => 'annual', 'annual_entitlement_days' => 20, 'carry_forward_enabled' => true, 'carry_forward_cap_days' => 5],
            ['name' => 'Sick Leave', 'category' => 'sick', 'annual_entitlement_days' => 10, 'carry_forward_enabled' => false, 'carry_forward_cap_days' => null],
            ['name' => 'Maternity Leave', 'category' => 'maternity', 'annual_entitlement_days' => 120, 'carry_forward_enabled' => false, 'carry_forward_cap_days' => null],
            ['name' => 'Unpaid Leave', 'category' => 'unpaid', 'annual_entitlement_days' => null, 'carry_forward_enabled' => false, 'carry_forward_cap_days' => null],
        ];

        $map = [];
        foreach ($items as $item) {
            $map[mb_strtolower($item['name'])] = (object) $item;
        }

        return $map;
    }
}
