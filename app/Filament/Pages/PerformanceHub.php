<?php

namespace App\Filament\Pages;

use App\Models\AnnualObjectiveRating;
use App\Models\AnnualPerformanceReview;
use App\Models\AnnualValueRating;
use App\Models\PerformanceObjective;
use App\Models\PerformanceReviewSetting;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

class PerformanceHub extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-line';
    protected static ?string $navigationGroup = 'Performance';
    protected static ?int $navigationSort = 10;
    protected static ?string $title = 'Performance Hub';
    protected static ?string $slug = 'performance-hub';

    protected static string $view = 'filament.pages.performance-hub';

    public int $year;

    public function mount(): void
    {
        $this->year = (int) now()->year;
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user && ($user->isAdmin() || $user->hasRole(['HR Manager', 'Operations Manager']));
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public function getSummary(): array
    {
        $base = AnnualPerformanceReview::query()->where('review_year', $this->year);

        return [
            'total_reviews' => (clone $base)->count(),
            'draft' => (clone $base)->where('status', 'draft')->count(),
            'self_submitted' => (clone $base)->where('status', 'self_submitted')->count(),
            'manager_finalized' => (clone $base)->where('status', 'manager_finalized')->count(),
            'active_employees' => User::query()->where('status', 'Active')->count(),
        ];
    }

    public function generateAnnualReviews(): void
    {
        $settings = PerformanceReviewSetting::current();
        $valuesCatalog = $settings->valuesCatalog();
        $activeEmployees = User::query()->where('status', 'Active')->get(['user_id']);

        $created = 0;
        $alreadyExists = 0;

        DB::transaction(function () use (
            $activeEmployees,
            $valuesCatalog,
            &$created,
            &$alreadyExists
        ) {
            foreach ($activeEmployees as $employee) {
                $review = AnnualPerformanceReview::query()->firstOrCreate(
                    ['user_id' => $employee->user_id, 'review_year' => $this->year],
                    ['status' => 'draft']
                );

                if ($review->wasRecentlyCreated) {
                    $created++;
                } else {
                    $alreadyExists++;
                }

                $objectives = $this->objectivesForYear((string) $employee->user_id, $this->year);
                foreach ($objectives as $objective) {
                    AnnualObjectiveRating::query()->firstOrCreate([
                        'annual_performance_review_id' => $review->id,
                        'performance_objective_id' => $objective->id,
                    ]);
                }

                foreach ($valuesCatalog as $label) {
                    if (! is_string($label) || trim($label) === '') {
                        continue;
                    }
                    $normalizedLabel = trim($label);
                    $key = str($normalizedLabel)->slug()->toString();
                    AnnualValueRating::query()->firstOrCreate([
                        'annual_performance_review_id' => $review->id,
                        'value_key' => $key,
                    ], [
                        'value_label' => $normalizedLabel,
                    ]);
                }
            }
        });

        Notification::make()
            ->title('Annual reviews generated')
            ->body(sprintf(
                'Year %d: %d active employees processed (%d new, %d existing).',
                $this->year,
                $activeEmployees->count(),
                $created,
                $alreadyExists
            ))
            ->success()
            ->send();
    }

    public function getLegacyCutoverMap(): array
    {
        return (array) config('legacy_admin_cutover.legacy_to_filament', []);
    }

    private function objectivesForYear(string $userId, int $year)
    {
        return PerformanceObjective::query()
            ->where('user_id', $userId)
            ->whereYear('period_start', '<=', $year)
            ->whereYear('period_end', '>=', $year)
            ->whereIn('period_type', ['quarterly', 'biannual', 'annual'])
            ->orderBy('period_start')
            ->get();
    }
}
