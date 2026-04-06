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
            'values_catalog_json' => json_encode(self::defaultValuesCatalogDetailed()),
        ]);
    }

    public function valuesCatalog(): array
    {
        $normalized = [];
        foreach ($this->valuesCatalogDetailed() as $item) {
            $label = trim((string) ($item['label'] ?? ''));
            if ($label !== '') {
                $normalized[] = $label;
            }
        }

        return array_values(array_unique($normalized));
    }

    public function valuesCatalogDetailed(): array
    {
        $decoded = json_decode((string) ($this->values_catalog_json ?? '[]'), true);
        if (! is_array($decoded) || $decoded === []) {
            return self::defaultValuesCatalogDetailed();
        }

        $normalized = [];
        foreach ($decoded as $item) {
            if (is_string($item)) {
                $title = trim($item);
                $code = $title !== '' ? strtoupper(substr($title, 0, 1)) : '';
                $description = '';
            } elseif (is_array($item)) {
                $code = strtoupper(trim((string) ($item['code'] ?? $item['key'] ?? $item['letter'] ?? '')));
                $title = trim((string) ($item['title'] ?? $item['name'] ?? $item['value'] ?? $item['label'] ?? ''));
                $description = trim((string) ($item['description'] ?? $item['details'] ?? ''));
            } else {
                $code = '';
                $title = '';
                $description = '';
            }

            if ($title === '') {
                continue;
            }

            $normalized[] = [
                'code' => $code,
                'title' => $title,
                'label' => $code !== '' ? ($code . ' - ' . $title) : $title,
                'description' => $description,
            ];
        }

        if ($normalized === []) {
            return self::defaultValuesCatalogDetailed();
        }

        return $normalized;
    }

    private static function defaultValuesCatalogDetailed(): array
    {
        return [
            [
                'code' => 'P',
                'title' => 'Passionate',
                'label' => 'P - Passionate',
                'description' => "Passion is the heartbeat of Purple Crayola. We don't just work — we immerse ourselves, pouring heart and soul into every project. This unwavering passion energises us, pushing boundaries and catalysing innovation. It's what prompts us to rise each morning with renewed zest, eager to make a mark and inspire those around us.",
            ],
            [
                'code' => 'U',
                'title' => 'Unique',
                'label' => 'U - Unique',
                'description' => 'In a world of conformity, Purple Crayola is an emblem of uniqueness. We champion originality, fostering a culture where distinct ideas, perspectives, and voices are celebrated. We believe that our differences unite us, spurring creativity and adding depth to our collective identity.',
            ],
            [
                'code' => 'R',
                'title' => 'Resolute',
                'label' => 'R - Resolute',
                'description' => 'Steadfastness is in our DNA. Challenges, while inevitable, are mere hurdles waiting to be overcome. Armed with determination, we face adversities head-on, using them as stepping stones toward greater success. Our unwavering commitment to our goals, coupled with an indomitable spirit, ensures that we remain resolute in our pursuit of excellence.',
            ],
            [
                'code' => 'P',
                'title' => 'People-driven',
                'label' => 'P - People-driven',
                'description' => 'We believe that the true essence of Purple Crayola lies in its people. From the dedicated teams crafting our products to the valued customers who trust in our brand, people are our greatest asset. We are committed to nurturing these relationships, ensuring we create environments that listen, understand, and prioritise the well-being of everyone involved.',
            ],
            [
                'code' => 'L',
                'title' => 'Leadership',
                'label' => 'L - Leadership',
                'description' => "Leadership at Purple Crayola is more than just a title or position. It's a mindset, a responsibility that permeates every level of our organisation. We cultivate leaders, not followers — encouraging everyone, irrespective of their role, to take charge, make decisions, and drive change. By fostering leadership at all tiers, we ensure a dynamic, proactive, and empowered workforce ready to chart new territories.",
            ],
            [
                'code' => 'E',
                'title' => 'Enterprising',
                'label' => 'E - Enterprising',
                'description' => "At the heart of Purple Crayola is an enterprising spirit that constantly yearns for growth and evolution. We are never static, always on the move, scouting for the next big idea or opportunity. This proactive approach means we're not just adapting to change; we're often the catalysts, shaping the future with our innovative endeavours.",
            ],
        ];
    }
}
