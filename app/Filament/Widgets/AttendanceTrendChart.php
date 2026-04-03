<?php

namespace App\Filament\Widgets;

use App\Models\AttendanceRecord;
use App\Models\User;
use Filament\Widgets\ChartWidget;

class AttendanceTrendChart extends ChartWidget
{
    protected static ?string $heading = 'Attendance Trend (Last 7 Days)';

    protected int|string|array $columnSpan = 'full';

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
        $days = collect(range(6, 0))
            ->map(fn (int $offset) => now()->subDays($offset)->toDateString());

        $counts = AttendanceRecord::query()
            ->whereIn('attendance_date', $days)
            ->selectRaw('attendance_date, COUNT(*) as total')
            ->groupBy('attendance_date')
            ->pluck('total', 'attendance_date');

        return [
            'datasets' => [
                [
                    'label' => 'Check-ins',
                    'data' => $days->map(fn (string $date) => (int) ($counts[$date] ?? 0))->all(),
                    'borderColor' => '#4f46e5',
                    'backgroundColor' => 'rgba(79, 70, 229, 0.12)',
                    'fill' => true,
                    'tension' => 0.35,
                ],
            ],
            'labels' => $days->map(fn (string $date) => \Carbon\Carbon::parse($date)->format('D'))->all(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
