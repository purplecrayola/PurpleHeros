<?php

namespace App\Filament\Widgets;

use App\Models\StaffSalary;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PayrollSnapshotOverview extends BaseWidget
{
    public static function canView(): bool
    {
        $user = auth()->user();

        return $user instanceof User && $user->canManagePayroll();
    }

    protected function getStats(): array
    {
        $recordCount = StaffSalary::query()->count();
        $totalSalary = (float) StaffSalary::query()->sum('salary');
        $averageSalary = $recordCount > 0 ? $totalSalary / $recordCount : 0.0;

        return [
            Stat::make('Payroll Records', (string) $recordCount),
            Stat::make('Total Salary', number_format($totalSalary, 2)),
            Stat::make('Average Salary', number_format($averageSalary, 2)),
        ];
    }
}
