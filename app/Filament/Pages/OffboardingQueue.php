<?php

namespace App\Filament\Pages;

use App\Models\EmployeeOffboarding;
use App\Support\OffboardingNotificationManager;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class OffboardingQueue extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-user-minus';
    protected static ?string $navigationGroup = 'People';
    protected static ?int $navigationSort = 25;
    protected static ?string $title = 'Offboarding Queue';
    protected static ?string $slug = 'offboarding-queue';

    protected static string $view = 'filament.pages.offboarding-queue';

    public string $search = '';
    public string $statusFilter = '';
    public string $dueFilter = 'all';

    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->canManagePeopleOps();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public function getSummary(): array
    {
        $base = EmployeeOffboarding::query();

        return [
            'active' => (clone $base)->whereIn('offboarding_status', ['planned', 'in_progress'])->count(),
            'due_this_week' => (clone $base)
                ->whereNotNull('last_working_day')
                ->whereDate('last_working_day', '>=', now()->startOfWeek())
                ->whereDate('last_working_day', '<=', now()->endOfWeek())
                ->whereIn('offboarding_status', ['planned', 'in_progress'])
                ->count(),
            'overdue' => (clone $base)
                ->whereNotNull('last_working_day')
                ->whereDate('last_working_day', '<', now()->toDateString())
                ->whereIn('offboarding_status', ['planned', 'in_progress'])
                ->count(),
            'completed_this_month' => (clone $base)
                ->where('offboarding_status', 'completed')
                ->whereNotNull('completed_at')
                ->whereBetween('completed_at', [now()->startOfMonth(), now()->endOfMonth()])
                ->count(),
        ];
    }

    public function getRows(): Collection
    {
        return $this->queueQuery()
            ->with('user:user_id,name,department,position,status')
            ->orderByRaw("CASE offboarding_status
                WHEN 'in_progress' THEN 1
                WHEN 'planned' THEN 2
                WHEN 'not_started' THEN 3
                WHEN 'completed' THEN 4
                ELSE 5 END")
            ->orderBy('last_working_day')
            ->limit(200)
            ->get();
    }

    public function getStatusOptions(): array
    {
        return [
            '' => 'All statuses',
            'not_started' => 'Not Started',
            'planned' => 'Planned',
            'in_progress' => 'In Progress',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
        ];
    }

    public function getDueFilterOptions(): array
    {
        return [
            'all' => 'All',
            'due_this_week' => 'Due this week',
            'overdue' => 'Overdue',
            'completed_this_month' => 'Completed this month',
        ];
    }

    public function toggleChecklist(int $recordId, string $field): void
    {
        $allowed = [
            'exit_interview_completed',
            'knowledge_transfer_completed',
            'assets_returned',
            'access_revoked',
            'final_settlement_completed',
        ];

        if (! in_array($field, $allowed, true)) {
            return;
        }

        $record = EmployeeOffboarding::query()->find($recordId);
        if (! $record) {
            return;
        }

        $record->{$field} = ! (bool) $record->{$field};

        if ($record->offboarding_status === 'not_started') {
            $record->offboarding_status = 'in_progress';
            $record->initiated_by_user_id = $record->initiated_by_user_id ?: auth()->user()?->user_id;
        }

        $record->save();

        Notification::make()
            ->title('Checklist updated')
            ->success()
            ->send();
    }

    public function markCompleted(int $recordId): void
    {
        $record = EmployeeOffboarding::query()->find($recordId);
        if (! $record) {
            return;
        }

        $previousStatus = (string) ($record->offboarding_status ?: 'not_started');
        $record->offboarding_status = 'completed';
        $record->completed_at = now();
        $record->completed_by_user_id = auth()->user()?->user_id;
        $record->save();

        if (strtolower($previousStatus) !== 'completed') {
            OffboardingNotificationManager::sendStatusTransition($record, $previousStatus, 'completed');
        }

        Notification::make()
            ->title('Offboarding completed')
            ->success()
            ->send();
    }

    private function queueQuery(): Builder
    {
        return EmployeeOffboarding::query()
            ->when($this->search !== '', function (Builder $query): void {
                $term = $this->search;
                $query->where(function (Builder $inner) use ($term): void {
                    $inner->where('user_id', 'like', "%{$term}%")
                        ->orWhere('offboarding_reason', 'like', "%{$term}%")
                        ->orWhereHas('user', function (Builder $userQuery) use ($term): void {
                            $userQuery->where('name', 'like', "%{$term}%")
                                ->orWhere('department', 'like', "%{$term}%")
                                ->orWhere('position', 'like', "%{$term}%");
                        });
                });
            })
            ->when($this->statusFilter !== '', fn (Builder $query): Builder => $query->where('offboarding_status', $this->statusFilter))
            ->when($this->dueFilter === 'due_this_week', function (Builder $query): Builder {
                return $query
                    ->whereNotNull('last_working_day')
                    ->whereDate('last_working_day', '>=', now()->startOfWeek())
                    ->whereDate('last_working_day', '<=', now()->endOfWeek())
                    ->whereIn('offboarding_status', ['planned', 'in_progress']);
            })
            ->when($this->dueFilter === 'overdue', function (Builder $query): Builder {
                return $query
                    ->whereNotNull('last_working_day')
                    ->whereDate('last_working_day', '<', now()->toDateString())
                    ->whereIn('offboarding_status', ['planned', 'in_progress']);
            })
            ->when($this->dueFilter === 'completed_this_month', function (Builder $query): Builder {
                return $query
                    ->where('offboarding_status', 'completed')
                    ->whereNotNull('completed_at')
                    ->whereBetween('completed_at', [now()->startOfMonth(), now()->endOfMonth()]);
            });
    }
}
