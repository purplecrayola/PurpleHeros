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
    public string $values_catalog_lines = '';

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
        $this->values_catalog_lines = implode(PHP_EOL, $settings->valuesCatalog());
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
            'values_catalog_lines' => 'nullable|string|max:5000',
        ]);

        if (((int) $validated['objective_weight'] + (int) $validated['values_weight']) !== 100) {
            Notification::make()
                ->title('Validation error')
                ->body('Objectives weight plus Values weight must equal 100.')
                ->danger()
                ->send();
            return;
        }

        $valueLines = collect(preg_split('/\r\n|\r|\n/', (string) ($validated['values_catalog_lines'] ?? '')))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values()
            ->all();
        if ($valueLines === []) {
            $valueLines = ['Integrity', 'Collaboration', 'Ownership', 'Innovation', 'Customer Focus'];
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
            'values_catalog_json' => json_encode($valueLines),
        ]);

        Notification::make()
            ->title('Saved')
            ->body('Performance settings updated.')
            ->success()
            ->send();
    }
}
