<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\AttendanceRecord;
use App\Models\LeavesAdmin;
use App\Models\OvertimeEntry;
use App\Models\StaffSalary;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class HrSnapshotOverview extends BaseWidget
{
    public static function canView(): bool
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            return false;
        }

        return $user->canManagePeopleOps()
            || $user->canManageTimeAttendance()
            || $user->canManagePayroll()
            || $user->canViewReports();
    }

    protected function getStats(): array
    {
        $today = now()->toDateString();

        return [
            Stat::make('Today Attendance', (string) AttendanceRecord::query()->whereDate('attendance_date', $today)->count()),
            Stat::make('Pending Leave Requests', (string) LeavesAdmin::query()->where('to_date', '>=', $today)->count()),
            Stat::make('Approved Overtime (hrs)', (string) number_format((float) OvertimeEntry::query()->where('status', 'Approved')->sum('hours'), 2)),
            Stat::make('Payroll Records', (string) StaffSalary::query()->count()),
        ];
    }
}
