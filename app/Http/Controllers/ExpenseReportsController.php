<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\Employee;
use App\Models\LeavesAdmin;
use App\Models\StaffSalary;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ExpenseReportsController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            abort_unless(Auth::user()?->isAdmin(), 403);

            return $next($request);
        });
    }

    public function index()
    {
        return view('reports.deferred', [
            'title' => 'Expense Report',
            'summary' => 'Expense reporting is not part of the shipped Purple HR SMB v1 surface.',
        ]);
    }

    public function invoiceReports()
    {
        return view('reports.deferred', [
            'title' => 'Invoice Report',
            'summary' => 'Invoice reporting belongs to the deferred finance workflow set and is not currently shipped in SMB v1.',
        ]);
    }

    public function dailyReport(Request $request)
    {
        $reportDate = $request->input('report_date');
        $selectedDate = $reportDate ? Carbon::parse($reportDate)->toDateString() : (AttendanceRecord::query()->max('attendance_date') ?: Carbon::today()->toDateString());

        $attendanceRows = DB::table('attendance_records')
            ->join('users', 'users.user_id', '=', 'attendance_records.user_id')
            ->leftJoin('profile_information', 'profile_information.user_id', '=', 'users.user_id')
            ->select(
                'users.name',
                'users.user_id',
                'users.avatar',
                'users.department as user_department',
                'profile_information.department as profile_department',
                'attendance_records.attendance_date',
                'attendance_records.status',
                'attendance_records.check_in',
                'attendance_records.check_out',
                'attendance_records.notes'
            )
            ->whereDate('attendance_records.attendance_date', $selectedDate)
            ->orderBy('users.name')
            ->get();

        $summary = [
            'total_employees' => Employee::count(),
            'present' => $attendanceRows->where('status', 'Present')->count(),
            'remote' => $attendanceRows->where('status', 'Remote')->count(),
            'late' => $attendanceRows->where('status', 'Late')->count(),
            'absent' => $attendanceRows->where('status', 'Absent')->count(),
        ];

        return view('reports.dailyreports', [
            'selectedDate' => $selectedDate,
            'summary' => $summary,
            'attendanceRows' => $attendanceRows,
        ]);
    }

    public function leaveReport(Request $request)
    {
        $employee = trim((string) $request->input('employee'));

        $leaves = DB::table('leaves_admins')
            ->join('users', 'users.user_id', '=', 'leaves_admins.user_id')
            ->leftJoin('profile_information', 'profile_information.user_id', '=', 'users.user_id')
            ->select(
                'users.name',
                'users.user_id',
                'users.avatar',
                'users.department as user_department',
                'profile_information.department as profile_department',
                'leaves_admins.leave_type',
                'leaves_admins.from_date',
                'leaves_admins.to_date',
                'leaves_admins.day',
                'leaves_admins.leave_reason'
            )
            ->when($employee !== '', function ($query) use ($employee) {
                $query->where('users.name', 'like', "%{$employee}%")
                    ->orWhere('users.user_id', 'like', "%{$employee}%");
            })
            ->orderByDesc('leaves_admins.from_date')
            ->get();

        return view('reports.leavereports', [
            'leaves' => $leaves,
            'employee' => $employee,
        ]);
    }

    public function paymentsReportIndex()
    {
        return view('reports.deferred', [
            'title' => 'Payments Report',
            'summary' => 'Payments reporting is outside the current Purple HR SMB v1 HR operations scope.',
        ]);
    }

    public function employeeReportsIndex(Request $request)
    {
        $employee = trim((string) $request->input('employee'));
        $department = trim((string) $request->input('department'));

        $employees = DB::table('users')
            ->leftJoin('profile_information', 'profile_information.user_id', '=', 'users.user_id')
            ->leftJoin('staff_salaries', 'staff_salaries.user_id', '=', 'users.user_id')
            ->select(
                'users.name',
                'users.user_id',
                'users.email',
                'users.phone_number',
                'users.status',
                'users.join_date',
                'users.position',
                'users.department as user_department',
                'users.avatar',
                'profile_information.birth_date',
                'profile_information.gender',
                'profile_information.address',
                'profile_information.department as profile_department',
                'profile_information.designation',
                'staff_salaries.salary'
            )
            ->when($employee !== '', function ($query) use ($employee) {
                $query->where('users.name', 'like', "%{$employee}%")
                    ->orWhere('users.user_id', 'like', "%{$employee}%")
                    ->orWhere('users.email', 'like', "%{$employee}%");
            })
            ->when($department !== '', function ($query) use ($department) {
                $query->where(function ($innerQuery) use ($department) {
                    $innerQuery->where('users.department', $department)
                        ->orWhere('profile_information.department', $department);
                });
            })
            ->orderBy('users.name')
            ->get();

        $departments = DB::table('users')
            ->whereNotNull('department')
            ->where('department', '!=', '')
            ->distinct()
            ->orderBy('department')
            ->pluck('department');

        return view('reports.employee-reports', [
            'employees' => $employees,
            'departments' => $departments,
            'employee' => $employee,
            'department' => $department,
        ]);
    }
}
