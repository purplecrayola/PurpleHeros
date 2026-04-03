<?php

namespace App\Filament\Pages;

use App\Exports\ArrayReportExport;
use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Models\ExportAudit;
use App\Models\LeavesAdmin;
use App\Models\StaffSalary;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportsHub extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationGroup = 'Reports';
    protected static ?int $navigationSort = 10;
    protected static ?string $title = 'Reports Hub';
    protected static ?string $slug = 'reports-hub';

    protected static string $view = 'filament.pages.reports-hub';

    public string $reportDate = '';
    public string $employeeSearch = '';

    public function mount(): void
    {
        $this->reportDate = (string) (AttendanceRecord::query()->max('attendance_date') ?: now()->toDateString());
    }

    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->canViewReports();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->check() && auth()->user()->canViewReports();
    }

    public function getSummary(): array
    {
        $attendanceRows = DB::table('attendance_records')
            ->whereDate('attendance_date', $this->reportDate)
            ->get();

        return [
            'total_employees' => Employee::query()->count(),
            'present' => $attendanceRows->where('status', 'Present')->count(),
            'remote' => $attendanceRows->where('status', 'Remote')->count(),
            'late' => $attendanceRows->where('status', 'Late')->count(),
            'absent' => $attendanceRows->where('status', 'Absent')->count(),
            'leave_requests' => LeavesAdmin::query()->count(),
            'payroll_records' => StaffSalary::query()->count(),
        ];
    }

    public function getEmployeeRows()
    {
        return DB::table('users')
            ->leftJoin('profile_information', 'profile_information.user_id', '=', 'users.user_id')
            ->leftJoin('staff_salaries', 'staff_salaries.user_id', '=', 'users.user_id')
            ->select(
                'users.name',
                'users.user_id',
                'users.email',
                'users.status',
                'users.department as user_department',
                'users.position',
                'profile_information.department as profile_department',
                'profile_information.designation',
                'staff_salaries.salary'
            )
            ->when($this->employeeSearch !== '', function ($query) {
                $search = $this->employeeSearch;
                $query->where(function ($inner) use ($search) {
                    $inner->where('users.name', 'like', "%{$search}%")
                        ->orWhere('users.user_id', 'like', "%{$search}%")
                        ->orWhere('users.email', 'like', "%{$search}%");
                });
            })
            ->orderBy('users.name')
            ->limit(25)
            ->get();
    }

    public function getLeaveRows()
    {
        return DB::table('leaves_admins')
            ->join('users', 'users.user_id', '=', 'leaves_admins.user_id')
            ->select(
                'users.name',
                'users.user_id',
                'leaves_admins.leave_type',
                'leaves_admins.from_date',
                'leaves_admins.to_date',
                'leaves_admins.day',
                'leaves_admins.leave_reason'
            )
            ->when($this->employeeSearch !== '', function ($query) {
                $search = $this->employeeSearch;
                $query->where(function ($inner) use ($search) {
                    $inner->where('users.name', 'like', "%{$search}%")
                        ->orWhere('users.user_id', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('leaves_admins.from_date')
            ->limit(25)
            ->get();
    }

    public function getAttendanceRows()
    {
        return DB::table('attendance_records')
            ->join('users', 'users.user_id', '=', 'attendance_records.user_id')
            ->select(
                'users.name',
                'users.user_id',
                'users.department',
                'attendance_records.attendance_date',
                'attendance_records.status',
                'attendance_records.check_in',
                'attendance_records.check_out',
                'attendance_records.notes'
            )
            ->whereDate('attendance_records.attendance_date', $this->reportDate)
            ->when($this->employeeSearch !== '', function ($query) {
                $search = $this->employeeSearch;
                $query->where(function ($inner) use ($search) {
                    $inner->where('users.name', 'like', "%{$search}%")
                        ->orWhere('users.user_id', 'like', "%{$search}%");
                });
            })
            ->orderBy('users.name')
            ->limit(25)
            ->get();
    }

    public function exportEmployees(string $format = 'csv')
    {
        [$headers, $rows] = $this->employeeExportData();

        return $this->buildExportResponse(
            title: 'Employee Report',
            headers: $headers,
            rows: $rows,
            filenameBase: 'employee-report',
            format: $format,
        );
    }

    public function exportLeaves(string $format = 'csv')
    {
        [$headers, $rows] = $this->leaveExportData();

        return $this->buildExportResponse(
            title: 'Leave Report',
            headers: $headers,
            rows: $rows,
            filenameBase: 'leave-report',
            format: $format,
        );
    }

    public function exportAttendance(string $format = 'csv')
    {
        [$headers, $rows] = $this->attendanceExportData();

        return $this->buildExportResponse(
            title: 'Daily Attendance Report',
            headers: $headers,
            rows: $rows,
            filenameBase: 'daily-attendance-report',
            format: $format,
        );
    }

    private function employeeExportData(): array
    {
        $headers = ['Employee', 'Employee ID', 'Email', 'Department', 'Designation', 'Status', 'Salary'];
        $rows = $this->getEmployeeRows()->map(function ($row) {
            return [
                $row->name,
                $row->user_id,
                $row->email,
                $row->profile_department ?: $row->user_department ?: 'Unassigned',
                $row->designation ?: $row->position ?: 'Not set',
                $row->status ?: 'Active',
                $row->salary ?: 'N/A',
            ];
        })->all();

        return [$headers, $rows];
    }

    private function leaveExportData(): array
    {
        $headers = ['Employee', 'Employee ID', 'Type', 'From Date', 'To Date', 'Days', 'Reason'];
        $rows = $this->getLeaveRows()->map(function ($row) {
            return [
                $row->name,
                $row->user_id,
                $row->leave_type,
                $row->from_date,
                $row->to_date,
                $row->day,
                $row->leave_reason ?: 'N/A',
            ];
        })->all();

        return [$headers, $rows];
    }

    private function attendanceExportData(): array
    {
        $headers = ['Employee', 'Employee ID', 'Date', 'Department', 'Status', 'Check In', 'Check Out', 'Notes'];
        $rows = $this->getAttendanceRows()->map(function ($row) {
            return [
                $row->name,
                $row->user_id,
                $row->attendance_date,
                $row->department ?: 'Unassigned',
                ucfirst((string) $row->status),
                $row->check_in ?: 'N/A',
                $row->check_out ?: 'N/A',
                $row->notes ?: 'N/A',
            ];
        })->all();

        return [$headers, $rows];
    }

    private function buildExportResponse(string $title, array $headers, array $rows, string $filenameBase, string $format)
    {
        $format = strtolower($format);
        $filename = "{$filenameBase}.{$format}";

        $this->recordExportAudit($title, $format, $filename);

        if ($format === 'xlsx') {
            return Excel::download(
                new ArrayReportExport([$headers, ...$rows]),
                $filename
            );
        }

        if ($format === 'pdf') {
            return Pdf::loadView('exports.report-table', [
                'title' => $title,
                'headers' => $headers,
                'rows' => $rows,
            ])->download($filename);
        }

        return $this->streamCsv($headers, $rows, $filename);
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

    private function recordExportAudit(string $reportName, string $format, string $filename): void
    {
        $user = auth()->user();

        ExportAudit::query()->create([
            'user_id' => $user?->user_id,
            'user_email' => $user?->email,
            'report_name' => $reportName,
            'format' => $format,
            'filename' => $filename,
            'report_date' => $this->reportDate,
            'employee_search' => $this->employeeSearch !== '' ? $this->employeeSearch : null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'exported_at' => now(),
        ]);
    }
}
