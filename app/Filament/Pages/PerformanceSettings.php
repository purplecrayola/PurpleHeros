<?php

namespace App\Filament\Pages;

use App\Models\PerformanceReviewSetting;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class PerformanceSettings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-adjustments-horizontal';
    protected static ?string $navigationGroup = 'Performance';
    protected static ?int $navigationSort = 20;
    protected static ?string $title = 'Performance Settings';
    protected static ?string $slug = 'performance-settings';

    protected static string $view = 'filament.pages.performance-settings';

    public int $objective_weight = 70;
    public int $values_weight = 30;
    public bool $allow_employee_objectives = true;
    public bool $allow_manager_objectives = true;
    public int $monthly_update_due_day = 28;
    public int $weekly_update_due_weekday = 5;
    public bool $annual_section_objectives_enabled = true;
    public bool $annual_section_values_enabled = true;
    public bool $annual_stage_manager_submit_required = true;
    public bool $annual_stage_calibration_enabled = false;
    public bool $annual_stage_joint_review_enabled = true;
    public bool $annual_stage_employee_ack_required = true;
    public bool $annual_allow_admin_manual_progress = true;
    public array $values_catalog_items = [];
    public int $values_catalog_count = 0;
    public array $values_catalog_preview = [];
    public bool $values_catalog_has_duplicates = false;
    public array $values_catalog_duplicates = [];
    public ?string $last_saved_at = null;

    public function mount(): void
    {
        $settings = PerformanceReviewSetting::current();

        $this->objective_weight = (int) $settings->objective_weight;
        $this->values_weight = (int) $settings->values_weight;
        $this->allow_employee_objectives = (bool) $settings->allow_employee_objectives;
        $this->allow_manager_objectives = (bool) $settings->allow_manager_objectives;
        $this->monthly_update_due_day = (int) $settings->monthly_update_due_day;
        $this->weekly_update_due_weekday = (int) $settings->weekly_update_due_weekday;
        $this->annual_section_objectives_enabled = (bool) $settings->annual_section_objectives_enabled;
        $this->annual_section_values_enabled = (bool) $settings->annual_section_values_enabled;
        $this->annual_stage_manager_submit_required = (bool) $settings->annual_stage_manager_submit_required;
        $this->annual_stage_calibration_enabled = (bool) $settings->annual_stage_calibration_enabled;
        $this->annual_stage_joint_review_enabled = (bool) $settings->annual_stage_joint_review_enabled;
        $this->annual_stage_employee_ack_required = (bool) $settings->annual_stage_employee_ack_required;
        $this->annual_allow_admin_manual_progress = (bool) $settings->annual_allow_admin_manual_progress;
        $this->values_catalog_items = $settings->valuesCatalogDetailed();
        $this->parseValuesCatalogState();
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user && $user->canManageSettings();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public function save(): void
    {
        $validated = $this->validate([
            'objective_weight' => 'required|integer|min:0|max:100',
            'values_weight' => 'required|integer|min:0|max:100',
            'allow_employee_objectives' => 'required|boolean',
            'allow_manager_objectives' => 'required|boolean',
            'monthly_update_due_day' => 'required|integer|min:1|max:31',
            'weekly_update_due_weekday' => 'required|integer|min:1|max:7',
            'annual_section_objectives_enabled' => 'required|boolean',
            'annual_section_values_enabled' => 'required|boolean',
            'annual_stage_manager_submit_required' => 'required|boolean',
            'annual_stage_calibration_enabled' => 'required|boolean',
            'annual_stage_joint_review_enabled' => 'required|boolean',
            'annual_stage_employee_ack_required' => 'required|boolean',
            'annual_allow_admin_manual_progress' => 'required|boolean',
            'values_catalog_items' => 'nullable|array|max:24',
            'values_catalog_items.*.code' => 'nullable|string|max:8',
            'values_catalog_items.*.title' => 'nullable|string|max:80',
            'values_catalog_items.*.description' => 'nullable|string|max:1200',
        ]);

        if (((int) $validated['objective_weight'] + (int) $validated['values_weight']) !== 100) {
            Notification::make()
                ->title('Validation error')
                ->body('Objectives weight plus Values weight must equal 100.')
                ->danger()
                ->send();
            return;
        }

        $normalizedItems = collect($validated['values_catalog_items'] ?? [])
            ->map(function (array $item): array {
                $code = strtoupper(trim((string) ($item['code'] ?? '')));
                $title = trim((string) ($item['title'] ?? ''));
                $description = trim((string) ($item['description'] ?? ''));
                $label = $code !== '' ? ($code . ' - ' . $title) : $title;

                return [
                    'code' => $code,
                    'title' => $title,
                    'label' => $label,
                    'description' => $description,
                ];
            })
            ->filter(fn (array $item) => $item['title'] !== '')
            ->values()
            ->all();

        $normalizedCounts = [];
        foreach ($normalizedItems as $item) {
            $key = strtolower($item['title']);
            $normalizedCounts[$key] = ($normalizedCounts[$key] ?? 0) + 1;
        }
        $duplicates = collect($normalizedCounts)
            ->filter(fn ($count) => $count > 1)
            ->keys()
            ->values()
            ->all();
        if ($duplicates !== []) {
            Notification::make()
                ->title('Validation error')
                ->body('Values catalog contains duplicate entries. Remove duplicates and try again.')
                ->danger()
                ->send();
            return;
        }

        if ($normalizedItems === []) {
            $normalizedItems = $this->defaultValuesCatalogItems();
        }

        $settings = PerformanceReviewSetting::current();
        $settings->update([
            'objective_weight' => (int) $validated['objective_weight'],
            'values_weight' => (int) $validated['values_weight'],
            'allow_employee_objectives' => (bool) $validated['allow_employee_objectives'],
            'allow_manager_objectives' => (bool) $validated['allow_manager_objectives'],
            'monthly_update_due_day' => (int) $validated['monthly_update_due_day'],
            'weekly_update_due_weekday' => (int) $validated['weekly_update_due_weekday'],
            'annual_section_objectives_enabled' => (bool) $validated['annual_section_objectives_enabled'],
            'annual_section_values_enabled' => (bool) $validated['annual_section_values_enabled'],
            'annual_stage_manager_submit_required' => (bool) $validated['annual_stage_manager_submit_required'],
            'annual_stage_calibration_enabled' => (bool) $validated['annual_stage_calibration_enabled'],
            'annual_stage_joint_review_enabled' => (bool) $validated['annual_stage_joint_review_enabled'],
            'annual_stage_employee_ack_required' => (bool) $validated['annual_stage_employee_ack_required'],
            'annual_allow_admin_manual_progress' => (bool) $validated['annual_allow_admin_manual_progress'],
            'values_catalog_json' => json_encode($normalizedItems),
        ]);

        $this->values_catalog_items = $normalizedItems;
        $this->parseValuesCatalogState();
        $this->last_saved_at = now()->format('d M Y, h:i A');

        Notification::make()
            ->title('Saved')
            ->body('Performance settings updated.')
            ->success()
            ->send();
    }

    public function updatedValuesCatalogItems(): void
    {
        $this->parseValuesCatalogState();
    }

    public function resetValuesCatalogToDefault(): void
    {
        $this->values_catalog_items = $this->defaultValuesCatalogItems();
        $this->parseValuesCatalogState();
    }

    public function addCatalogValue(): void
    {
        $this->values_catalog_items[] = [
            'code' => '',
            'title' => '',
            'description' => '',
            'label' => '',
        ];
        $this->parseValuesCatalogState();
    }

    public function removeCatalogValue(int $index): void
    {
        if (! array_key_exists($index, $this->values_catalog_items)) {
            return;
        }

        unset($this->values_catalog_items[$index]);
        $this->values_catalog_items = array_values($this->values_catalog_items);
        $this->parseValuesCatalogState();
    }

    private function parseValuesCatalogState(): void
    {
        $items = collect($this->values_catalog_items)
            ->map(function (array $item): array {
                $code = strtoupper(trim((string) ($item['code'] ?? '')));
                $title = trim((string) ($item['title'] ?? ''));
                $description = trim((string) ($item['description'] ?? ''));

                return [
                    'code' => $code,
                    'title' => $title,
                    'description' => $description,
                    'label' => $code !== '' ? ($code . ' - ' . $title) : $title,
                ];
            })
            ->filter(fn (array $item) => $item['title'] !== '')
            ->values();

        $this->values_catalog_count = $items->count();
        $this->values_catalog_preview = $items->take(12)->all();

        $normalizedCounts = [];
        $canonical = [];
        foreach ($items as $item) {
            $key = strtolower($item['title']);
            $normalizedCounts[$key] = ($normalizedCounts[$key] ?? 0) + 1;
            $canonical[$key] = $canonical[$key] ?? $item['title'];
        }

        $this->values_catalog_duplicates = collect($normalizedCounts)
            ->filter(fn ($count) => $count > 1)
            ->keys()
            ->map(fn ($key) => $canonical[$key] ?? $key)
            ->values()
            ->all();

        $this->values_catalog_has_duplicates = $this->values_catalog_duplicates !== [];
    }

    private function defaultValuesCatalogItems(): array
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
