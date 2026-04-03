<?php

namespace App\Filament\Widgets;

use App\Models\LeavesAdmin;
use App\Models\User;
use Filament\Widgets\ChartWidget;

class LeaveVolumeChart extends ChartWidget
{
    protected static ?string $heading = 'Leave Requests (Last 8 Weeks)';

    public static function canView(): bool
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            return false;
        }

        return $user->canManageTimeAttendance() || $user->canViewReports();
    }

    protected function getData(): array
    {
        $start = now()->subWeeks(7)->startOfWeek();
        $labels = collect(range(0, 7))
            ->map(fn (int $week) => $start->copy()->addWeeks($week));

        $all = LeavesAdmin::query()
            ->whereNotNull('from_date')
            ->pluck('from_date');

        $buckets = $labels->mapWithKeys(fn ($date) => [$date->format('o-\WW') => 0]);

        foreach ($all as $fromDate) {
            try {
                $weekKey = \Carbon\Carbon::parse($fromDate)->startOfWeek()->format('o-\WW');
                if ($buckets->has($weekKey)) {
                    $buckets[$weekKey]++;
                }
            } catch (\Throwable $exception) {
                // Skip legacy malformed dates.
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Requests',
                    'data' => $buckets->values()->all(),
                    'backgroundColor' => '#0ea5e9',
                    'borderColor' => '#0369a1',
                ],
            ],
            'labels' => $labels->map(fn ($date) => $date->format('M d'))->all(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
