<?php

namespace App\Filament\Pages;

use App\Exports\ArrayReportExport;
use App\Models\LeavePolicyBand;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Filament\Pages\Page;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LeaveBalanceReport extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-table-cells';
    protected static ?string $navigationGroup = 'Reports';
    protected static ?int $navigationSort = 20;
    protected static ?string $title = 'Leave Balance by Employee';
    protected static ?string $slug = 'leave-balance-report';

    protected static string $view = 'filament.pages.leave-balance-report';

    public int $year;
    public string $search = '';
    public string $department = '';

    public function mount(): void
    {
        $this->year = (int) now()->year;
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user && ($user->canViewReports() || $user->canManageTimeAttendance());
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public function getBands(): Collection
    {
        $bands = LeavePolicyBand::query()
            ->active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'category', 'annual_entitlement_days', 'carry_forward_enabled', 'carry_forward_cap_days']);

        if ($bands->isNotEmpty()) {
            return $bands;
        }

        return collect(LeavePolicyBand::fallbackOptions())
            ->map(function (string $name, string $key, int $index) {
                return (object) [
                    'id' => $index + 1,
                    'name' => $key,
                    'category' => match ($key) {
                        'Annual Leave' => 'annual',
                        'Sick Leave' => 'sick',
                        'Maternity Leave' => 'maternity',
                        'Unpaid Leave' => 'unpaid',
                        default => 'other',
                    },
                    'annual_entitlement_days' => $key === 'Unpaid Leave' ? null : ($key === 'Maternity Leave' ? 120 : ($key === 'Sick Leave' ? 10 : 20)),
                    'carry_forward_enabled' => $key === 'Annual Leave',
                    'carry_forward_cap_days' => $key === 'Annual Leave' ? 5 : null,
                ];
            })
            ->values();
    }

    public function getRows(): Collection
    {
        $bands = $this->getBands();
        $bandNames = $bands->pluck('name')->all();

        $usageByUser = DB::table('leaves_admins')
            ->select('user_id', 'leave_type', DB::raw('SUM(COALESCE(day, 0)) as used_days'))
            ->whereIn('leave_type', $bandNames)
            ->where('status', '!=', 'Rejected')
            ->whereRaw('substr(from_date, 1, 4) = ?', [(string) $this->year])
            ->groupBy('user_id', 'leave_type')
            ->get()
            ->groupBy('user_id');

        $users = User::query()
            ->select(['user_id', 'name', 'department', 'position', 'role_name', 'status'])
            ->whereNotIn('role_name', ['Admin', 'Super Admin'])
            ->when($this->search !== '', function ($query) {
                $search = $this->search;
                $query->where(function ($inner) use ($search) {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('user_id', 'like', "%{$search}%");
                });
            })
            ->when($this->department !== '', fn ($query) => $query->where('department', $this->department))
            ->orderBy('name')
            ->get();

        return $users->map(function (User $user) use ($bands, $usageByUser) {
            $userUsageRows = collect($usageByUser->get($user->user_id, []));

            $bandCells = [];
            $totalUsed = 0.0;

            foreach ($bands as $band) {
                $used = (float) ($userUsageRows->firstWhere('leave_type', $band->name)->used_days ?? 0);
                $entitlement = $band->annual_entitlement_days;
                if ($entitlement !== null && (bool) ($band->carry_forward_enabled ?? false)) {
                    $entitlement += (int) ($band->carry_forward_cap_days ?? 0);
                }

                $remaining = $entitlement === null ? null : max((float) $entitlement - $used, 0);
                $totalUsed += $used;

                $bandCells[$band->name] = [
                    'entitlement' => $entitlement,
                    'used' => $used,
                    'remaining' => $remaining,
                ];
            }

            return (object) [
                'user_id' => $user->user_id,
                'name' => $user->name,
                'department' => $user->department ?: 'Unassigned',
                'position' => $user->position ?: 'Not set',
                'total_used' => $totalUsed,
                'bands' => $bandCells,
            ];
        });
    }

    public function getDepartmentOptions(): array
    {
        return User::query()
            ->whereNotIn('role_name', ['Admin', 'Super Admin'])
            ->whereNotNull('department')
            ->where('department', '!=', '')
            ->orderBy('department')
            ->pluck('department', 'department')
            ->all();
    }

    public function getYearOptions(): array
    {
        $current = (int) now()->year;

        return collect(range($current - 3, $current + 1))
            ->mapWithKeys(fn (int $year) => [$year => (string) $year])
            ->all();
    }

    public function exportBalance(string $format = 'csv')
    {
        [$headers, $rows] = $this->leaveBalanceExportData();

        $format = strtolower($format);
        $filename = "leave-balance-report-{$this->year}.{$format}";

        if ($format === 'pdf') {
            return Pdf::loadView('exports.report-table', [
                'title' => "Leave Balance by Employee ({$this->year})",
                'headers' => $headers,
                'rows' => $rows,
            ])->download($filename);
        }

        if ($format === 'xlsx') {
            return Excel::download(
                new ArrayReportExport([$headers, ...$rows]),
                $filename
            );
        }

        if ($format !== 'csv') {
            Notification::make()
                ->title('Unsupported format')
                ->body('Please export as CSV, XLSX, or PDF.')
                ->danger()
                ->send();
            return null;
        }

        return $this->streamCsv($headers, $rows, $filename);
    }

    private function leaveBalanceExportData(): array
    {
        $bands = $this->getBands();
        $rows = $this->getRows();

        $headers = ['Employee', 'Employee ID', 'Department', 'Position'];
        foreach ($bands as $band) {
            $headers[] = "{$band->name} Entitlement";
            $headers[] = "{$band->name} Used";
            $headers[] = "{$band->name} Remaining";
        }
        $headers[] = 'Total Used';

        $dataRows = $rows->map(function ($row) use ($bands): array {
            $line = [
                $row->name,
                $row->user_id,
                $row->department,
                $row->position,
            ];

            foreach ($bands as $band) {
                $cell = $row->bands[$band->name];
                $line[] = $cell['entitlement'] === null ? 'Uncapped' : number_format((float) $cell['entitlement'], 1);
                $line[] = number_format((float) $cell['used'], 1);
                $line[] = $cell['remaining'] === null ? 'N/A' : number_format((float) $cell['remaining'], 1);
            }

            $line[] = number_format((float) $row->total_used, 1);

            return $line;
        })->all();

        return [$headers, $dataRows];
    }

    private function streamCsv(array $headers, array $rows, string $filename): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, $headers);
            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
