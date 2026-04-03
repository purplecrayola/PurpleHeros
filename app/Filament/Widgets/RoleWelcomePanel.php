<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\Widget;

class RoleWelcomePanel extends Widget
{
    protected static string $view = 'filament.widgets.role-welcome-panel';

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        $user = auth()->user();

        return $user instanceof User && $user->canAccessHrPanel();
    }

    protected function getViewData(): array
    {
        /** @var User $user */
        $user = auth()->user();

        return [
            'greeting' => $this->getGreeting(),
            'roleLabel' => (string) $user->role_name,
            'actionItems' => $this->buildActionItems($user),
        ];
    }

    private function getGreeting(): string
    {
        $hour = (int) now()->format('H');

        if ($hour < 12) {
            return 'Good morning';
        }

        if ($hour < 17) {
            return 'Good afternoon';
        }

        return 'Good evening';
    }

    private function buildActionItems(User $user): array
    {
        $items = [];

        if ($user->canManagePeopleOps()) {
            $items[] = ['label' => 'Employees', 'url' => url('/admin/employees')];
            $items[] = ['label' => 'Departments', 'url' => url('/admin/departments')];
        }

        if ($user->canManageTimeAttendance()) {
            $items[] = ['label' => 'Attendance', 'url' => url('/admin/attendance-records')];
            $items[] = ['label' => 'Leave Requests', 'url' => url('/admin/leaves-admins')];
            $items[] = ['label' => 'Overtime', 'url' => url('/admin/overtime-entries')];
        }

        if ($user->canManagePayroll()) {
            $items[] = ['label' => 'Payroll', 'url' => url('/admin/staff-salaries')];
        }

        if ($user->canViewReports()) {
            $items[] = ['label' => 'Reports Hub', 'url' => url('/admin/reports-hub')];
        }

        if ($user->canManageUsers()) {
            $items[] = ['label' => 'Users', 'url' => url('/admin/users')];
            $items[] = ['label' => 'Settings', 'url' => url('/admin/company-settings')];
        }

        return array_slice($items, 0, 8);
    }
}
