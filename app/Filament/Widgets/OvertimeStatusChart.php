<?php

namespace App\Filament\Widgets;

use App\Models\OvertimeEntry;
use App\Models\User;
use Filament\Widgets\ChartWidget;

class OvertimeStatusChart extends ChartWidget
{
    protected static ?string $heading = 'Overtime By Status';

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
        $labels = ['Pending', 'Approved', 'Rejected'];

        $counts = OvertimeEntry::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return [
            'datasets' => [
                [
                    'label' => 'Entries',
                    'data' => collect($labels)->map(fn (string $status) => (int) ($counts[$status] ?? 0))->all(),
                    'backgroundColor' => ['#f59e0b', '#10b981', '#ef4444'],
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
