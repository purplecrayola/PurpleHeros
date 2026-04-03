<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerformanceReviewSetting extends Model
{
    use HasFactory;

    protected $table = 'performance_review_settings';

    protected $fillable = [
        'objective_weight',
        'values_weight',
        'allow_employee_objectives',
        'allow_manager_objectives',
        'monthly_update_due_day',
        'weekly_update_due_weekday',
        'annual_section_objectives_enabled',
        'annual_section_values_enabled',
        'annual_stage_manager_submit_required',
        'annual_stage_calibration_enabled',
        'annual_stage_joint_review_enabled',
        'annual_stage_employee_ack_required',
        'annual_allow_admin_manual_progress',
        'values_catalog_json',
    ];

    public static function current(): self
    {
        return self::query()->firstOrCreate(['id' => 1], [
            'objective_weight' => 70,
            'values_weight' => 30,
            'allow_employee_objectives' => true,
            'allow_manager_objectives' => true,
            'monthly_update_due_day' => 28,
            'weekly_update_due_weekday' => 5,
            'annual_section_objectives_enabled' => true,
            'annual_section_values_enabled' => true,
            'annual_stage_manager_submit_required' => true,
            'annual_stage_calibration_enabled' => false,
            'annual_stage_joint_review_enabled' => true,
            'annual_stage_employee_ack_required' => true,
            'annual_allow_admin_manual_progress' => true,
            'values_catalog_json' => json_encode([
                'Integrity',
                'Collaboration',
                'Ownership',
                'Innovation',
                'Customer Focus',
            ]),
        ]);
    }

    public function valuesCatalog(): array
    {
        $decoded = json_decode((string) ($this->values_catalog_json ?? '[]'), true);
        if (! is_array($decoded) || $decoded === []) {
            return ['Integrity', 'Collaboration', 'Ownership', 'Innovation', 'Customer Focus'];
        }

        $normalized = [];
        foreach ($decoded as $item) {
            if (is_string($item)) {
                $label = trim($item);
            } elseif (is_array($item)) {
                $candidate = $item['label'] ?? $item['name'] ?? $item['title'] ?? $item['value'] ?? null;
                $label = is_string($candidate) ? trim($candidate) : '';
            } else {
                $label = '';
            }

            if ($label !== '') {
                $normalized[] = $label;
            }
        }

        if ($normalized === []) {
            return ['Integrity', 'Collaboration', 'Ownership', 'Innovation', 'Customer Focus'];
        }

        return array_values(array_unique($normalized));
    }
}
