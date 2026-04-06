<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\CompanySettings;
use App\Models\Employee;
use App\Models\Holiday;
use App\Models\LeavesAdmin;
use App\Models\OvertimeEntry;
use App\Models\StaffSalary;
use App\Models\TimesheetEntry;
use App\Models\User;
use App\Notifications\InAppNotification;
use App\Support\MailSettingsManager;
use App\Models\department;
use App\Models\positionType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use PDF;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            $user = Auth::user();
            $method = $request->route()?->getActionMethod();

            if ($method === 'index') {
                abort_unless($user && $user->isAdmin(), 403);
            }

            return $next($request);
        });
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        if (Auth::user()?->isAdmin()) {
            return redirect('/admin');
        }

        $now = Carbon::now();
        $greeting = $this->greetingForHour((int) $now->format('G'));

        $leaveTable = (new LeavesAdmin())->getTable();
        $attendanceTable = (new AttendanceRecord())->getTable();
        $timesheetTable = (new TimesheetEntry())->getTable();
        $overtimeTable = (new OvertimeEntry())->getTable();
        $salaryTable = (new StaffSalary())->getTable();

        $metrics = [
            'employees' => Employee::count(),
            'departments' => department::count(),
            'designations' => positionType::count(),
            'leave_requests' => LeavesAdmin::count(),
            'attendance_today' => AttendanceRecord::whereDate('attendance_date', $now->toDateString())->count(),
            'timesheet_hours_week' => (float) TimesheetEntry::whereBetween('work_date', [
                $now->copy()->startOfWeek()->toDateString(),
                $now->copy()->endOfWeek()->toDateString(),
            ])->sum('worked_hours'),
            'pending_overtime' => OvertimeEntry::where('status', 'Pending approval')->count(),
            'monthly_payroll' => (float) StaffSalary::sum('salary'),
        ];

        $recentLeaves = DB::table($leaveTable)
            ->leftJoin('users', 'users.user_id', '=', $leaveTable.'.user_id')
            ->select(
                DB::raw('COALESCE(users.name, '.$leaveTable.'.user_id) as employee_name'),
                $leaveTable.'.leave_type',
                $leaveTable.'.from_date',
                $leaveTable.'.to_date',
                $leaveTable.'.day'
            )
            ->orderByDesc($leaveTable.'.from_date')
            ->limit(5)
            ->get();

        $recentAttendance = DB::table($attendanceTable)
            ->leftJoin('users', 'users.user_id', '=', $attendanceTable.'.user_id')
            ->select(
                DB::raw('COALESCE(users.name, '.$attendanceTable.'.user_id) as employee_name'),
                $attendanceTable.'.attendance_date',
                $attendanceTable.'.status',
                $attendanceTable.'.check_in',
                $attendanceTable.'.check_out',
                $attendanceTable.'.notes'
            )
            ->orderByDesc($attendanceTable.'.attendance_date')
            ->limit(5)
            ->get();

        $recentTimesheets = DB::table($timesheetTable)
            ->leftJoin('users', 'users.user_id', '=', $timesheetTable.'.user_id')
            ->select(
                DB::raw('COALESCE(users.name, '.$timesheetTable.'.user_id) as employee_name'),
                $timesheetTable.'.work_date',
                $timesheetTable.'.project_name',
                $timesheetTable.'.worked_hours',
                $timesheetTable.'.description'
            )
            ->orderByDesc($timesheetTable.'.work_date')
            ->limit(5)
            ->get();

        $recentPayroll = DB::table($salaryTable)
            ->leftJoin('users', 'users.user_id', '=', $salaryTable.'.user_id')
            ->select(
                DB::raw('COALESCE(users.name, '.$salaryTable.'.name) as employee_name'),
                $salaryTable.'.user_id',
                $salaryTable.'.salary',
                $salaryTable.'.updated_at'
            )
            ->orderByDesc($salaryTable.'.updated_at')
            ->limit(5)
            ->get();

        return view('dashboard.dashboard', [
            'greeting' => $greeting,
            'todayLabel' => $now->format('l, M. d, Y'),
            'metrics' => $metrics,
            'recentLeaves' => $recentLeaves,
            'recentAttendance' => $recentAttendance,
            'recentTimesheets' => $recentTimesheets,
            'recentPayroll' => $recentPayroll,
        ]);
    }

    // employee dashboard
    public function emDashboard()
    {
        $user = Auth::user();
        $now = Carbon::now();
        $todayDate = $now->format('D, M d, Y g:i A');
        $todayLabel = $now->format('l, M j');

        $leaveCount = LeavesAdmin::where('user_id', $user->user_id)->count();
        $approvedAttendanceDays = AttendanceRecord::where('user_id', $user->user_id)
            ->whereMonth('attendance_date', $now->month)
            ->whereYear('attendance_date', $now->year)
            ->count();
        $timesheetHours = (float) TimesheetEntry::where('user_id', $user->user_id)
            ->whereBetween('work_date', [
                $now->copy()->startOfWeek()->toDateString(),
                $now->copy()->endOfWeek()->toDateString(),
            ])
            ->sum('worked_hours');
        $overtimeHours = (float) OvertimeEntry::where('user_id', $user->user_id)->sum('hours');

        $latestAttendance = AttendanceRecord::where('user_id', $user->user_id)
            ->orderByDesc('attendance_date')
            ->first();
        $latestLeave = LeavesAdmin::where('user_id', $user->user_id)
            ->orderByDesc('from_date')
            ->first();
        $latestTimesheet = TimesheetEntry::where('user_id', $user->user_id)
            ->orderByDesc('work_date')
            ->first();
        $latestOvertime = OvertimeEntry::where('user_id', $user->user_id)
            ->orderByDesc('ot_date')
            ->first();
        $pendingLeave = LeavesAdmin::where('user_id', $user->user_id)
            ->where('status', 'Pending')
            ->orderBy('from_date')
            ->first();
        $upcomingHoliday = Holiday::whereDate('date_holiday', '>=', $now->toDateString())
            ->orderBy('date_holiday')
            ->first();

        $quickActions = [
            ['label' => 'View Profile', 'route' => route('profile_user'), 'icon' => 'la la-id-card'],
            ['label' => 'Request Leave', 'route' => route('form/leavesemployee/new'), 'icon' => 'la la-calendar'],
            ['label' => 'View Attendance', 'route' => route('attendance/employee/page'), 'icon' => 'la la-clock-o'],
            ['label' => 'View Payslip', 'route' => route('my/payslips'), 'icon' => 'la la-credit-card'],
        ];

        $activityFeed = collect([
            $latestAttendance ? [
                'label' => 'Latest attendance',
                'value' => $latestAttendance->status . ' on ' . Carbon::parse($latestAttendance->attendance_date)->format('d M Y'),
            ] : null,
            $latestLeave ? [
                'label' => 'Latest leave request',
                'value' => $latestLeave->leave_type . ' from ' . Carbon::parse($latestLeave->from_date)->format('d M'),
            ] : null,
            $latestTimesheet ? [
                'label' => 'Latest timesheet',
                'value' => $latestTimesheet->project_name . ' logged for ' . Carbon::parse($latestTimesheet->work_date)->format('d M Y'),
            ] : null,
            $latestOvertime ? [
                'label' => 'Latest overtime',
                'value' => $latestOvertime->ot_type . ' (' . number_format((float) $latestOvertime->hours, 1) . ' hrs)',
            ] : null,
            $upcomingHoliday ? [
                'label' => 'Upcoming holiday',
                'value' => $upcomingHoliday->name_holiday . ' on ' . Carbon::parse($upcomingHoliday->date_holiday)->format('d M Y'),
            ] : null,
        ])->filter()->values();

        $metrics = [
            ['label' => 'Leave requests', 'value' => $leaveCount, 'helper' => 'Submitted so far'],
            ['label' => 'Attendance days', 'value' => $approvedAttendanceDays, 'helper' => 'Recorded this month'],
            ['label' => 'Timesheet hours', 'value' => number_format($timesheetHours, 1), 'helper' => 'Logged this week'],
            ['label' => 'Overtime hours', 'value' => number_format($overtimeHours, 1), 'helper' => 'Total tracked'],
        ];

        $attendanceValue = $latestAttendance ? ucfirst((string) $latestAttendance->status) : 'No records';
        $attendanceDetail = $latestAttendance && $latestAttendance->check_in
            ? 'Checked in at ' . Carbon::parse($latestAttendance->check_in)->format('g:i A')
            : 'No check-in time recorded';

        $leaveValue = $pendingLeave ? 'Pending leave' : 'No pending leave';
        $leaveDetail = $pendingLeave
            ? Carbon::parse($pendingLeave->from_date)->format('d M Y') . ' to ' . Carbon::parse($pendingLeave->to_date)->format('d M Y')
            : 'You are all caught up';

        $timesheetValue = $timesheetHours > 0 ? 'On track' : 'No entries this week';
        $timesheetDetail = $timesheetHours > 0
            ? number_format($timesheetHours, 1) . ' hours logged this week'
            : 'Submit this week\'s timesheet to stay compliant';

        $statusSignals = [
            ['label' => 'Attendance', 'value' => $attendanceValue, 'detail' => $attendanceDetail, 'icon' => 'la la-clock-o', 'tone' => 'positive'],
            ['label' => 'Leave', 'value' => $leaveValue, 'detail' => $leaveDetail, 'icon' => 'la la-leaf', 'tone' => $pendingLeave ? 'pending' : 'info'],
            ['label' => 'Timesheet', 'value' => $timesheetValue, 'detail' => $timesheetDetail, 'icon' => 'la la-file-text', 'tone' => $timesheetHours > 0 ? 'info' : 'pending'],
        ];

        $upcomingItems = collect([
            $upcomingHoliday ? [
                'title' => $upcomingHoliday->name_holiday,
                'detail' => 'Company holiday',
                'date' => Carbon::parse($upcomingHoliday->date_holiday)->format('D, M j'),
                'time' => 'All day',
                'icon' => 'la la-calendar',
                'route' => route('employee/holidays'),
                'tone' => 'purple',
            ] : null,
            $pendingLeave ? [
                'title' => 'Leave: ' . $pendingLeave->leave_type,
                'detail' => $pendingLeave->leave_reason ?: 'Awaiting approval',
                'date' => Carbon::parse($pendingLeave->from_date)->format('D, M j'),
                'time' => 'Pending',
                'icon' => 'la la-plane',
                'route' => route('form/leavesemployee/new'),
                'tone' => 'mint',
            ] : null,
            $latestTimesheet ? [
                'title' => 'Latest timesheet: ' . ($latestTimesheet->project_name ?: 'Work log'),
                'detail' => 'Most recent submission',
                'date' => Carbon::parse($latestTimesheet->work_date)->format('D, M j'),
                'time' => number_format((float) $latestTimesheet->worked_hours, 1) . ' hrs',
                'icon' => 'la la-file-text',
                'route' => route('employee/timesheets'),
                'tone' => 'purple',
            ] : null,
        ])->filter()->values();

        return view('dashboard.emdashboard', compact(
            'todayDate',
            'todayLabel',
            'user',
            'metrics',
            'quickActions',
            'activityFeed',
            'statusSignals',
            'upcomingItems',
            'latestAttendance',
            'latestLeave',
            'upcomingHoliday'
        ));
    }

    public function generatePDF(Request $request)
    {
        $pdf = PDF::loadView('payroll.salaryview');
        return $pdf->download('pdfview.pdf');
    }

    public function contactPeopleOps(Request $request)
    {
        $payload = $request->validate([
            'subject' => ['required', 'string', 'max:120'],
            'message' => ['required', 'string', 'max:4000'],
            'context_url' => ['nullable', 'url', 'max:2048'],
        ]);

        $user = Auth::user();
        $settings = CompanySettings::current();
        MailSettingsManager::apply($settings);

        $recipient = trim((string) (
            $settings->people_ops_email
            ?: $settings->mail_reply_to_address
            ?: $settings->email
            ?: 'heros@purplecrayola.com'
        ));

        if (! filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            $recipient = 'heros@purplecrayola.com';
        }

        $subject = '[People Ops] ' . $payload['subject'];
        $contextUrl = trim((string) ($payload['context_url'] ?? ''));
        $messageBody = trim((string) $payload['message']);

        $body = implode(PHP_EOL, [
            'People Ops request submitted from employee dashboard',
            '',
            'Employee: ' . ($user?->name ?: 'Unknown'),
            'Employee ID: ' . ($user?->user_id ?: 'Unknown'),
            'Employee Email: ' . ($user?->email ?: 'Unknown'),
            'Submitted At: ' . now()->toDateTimeString(),
            'Page Context: ' . ($contextUrl !== '' ? $contextUrl : 'N/A'),
            '',
            'Message:',
            $messageBody,
        ]);

        try {
            Mail::raw($body, function ($message) use ($recipient, $subject, $user): void {
                $message->to($recipient)->subject($subject);

                if ($user && filter_var((string) $user->email, FILTER_VALIDATE_EMAIL)) {
                    $message->replyTo((string) $user->email, (string) $user->name);
                }
            });

            if ($user) {
                $user->notify(new InAppNotification(
                    'People Ops request sent',
                    'Your support request has been sent successfully.',
                    route('profile_user'),
                    'success'
                ));
            }

            User::query()
                ->whereIn('role_name', ['Super Admin', 'Admin', 'HR Manager'])
                ->where('id', '!=', (int) ($user?->id ?? 0))
                ->get()
                ->each(function (User $admin) use ($user): void {
                    $admin->notify(new InAppNotification(
                        'New People Ops request',
                        ($user?->name ?: 'An employee') . ' sent a new support request.',
                        url('employee/profile/' . ($user?->user_id ?: '')),
                        'info'
                    ));
                });
        } catch (\Throwable $exception) {
            return back()->withErrors([
                'people_ops' => 'We could not send your request right now. Please try again shortly.',
            ])->withInput();
        }

        return back()->with('success', 'Your message has been sent to People Ops.');
    }

    public function globalSearch(Request $request)
    {
        $query = trim((string) $request->get('q', ''));
        $user = Auth::user();

        abort_unless($user !== null, 403);

        if ($query === '') {
            return redirect()->back();
        }

        $queryLower = mb_strtolower($query);
        $shortcuts = $this->buildSearchShortcuts($user)
            ->filter(function (array $shortcut) use ($queryLower) {
                $label = mb_strtolower($shortcut['label']);
                $keywords = collect($shortcut['keywords'])->map(fn ($keyword) => mb_strtolower($keyword));

                return str_contains($label, $queryLower)
                    || $keywords->contains(fn ($keyword) => str_contains($queryLower, $keyword) || str_contains($keyword, $queryLower));
            })
            ->values();

        $people = collect();
        if ($user->isAdmin()) {
            $people = User::query()
                ->where(function ($builder) use ($query) {
                    $builder->where('name', 'like', '%' . $query . '%')
                        ->orWhere('email', 'like', '%' . $query . '%')
                        ->orWhere('user_id', 'like', '%' . $query . '%');
                })
                ->orderBy('name')
                ->limit(20)
                ->get(['user_id', 'name', 'email', 'role_name', 'avatar']);
        } else {
            $selfFields = [
                mb_strtolower((string) $user->name),
                mb_strtolower((string) $user->email),
                mb_strtolower((string) $user->user_id),
                'me',
                'my profile',
                'profile',
            ];

            if (collect($selfFields)->contains(fn ($field) => str_contains($field, $queryLower) || str_contains($queryLower, $field))) {
                $people = collect([$user]);
            }
        }

        if ($shortcuts->count() === 1 && $people->isEmpty()) {
            return redirect($shortcuts->first()['url']);
        }

        if ($people->count() === 1 && $shortcuts->isEmpty() && $user->isAdmin()) {
            return redirect(url('employee/profile/' . $people->first()->user_id));
        }

        return view('search.global', [
            'query' => $query,
            'shortcuts' => $shortcuts,
            'people' => $people,
            'isAdmin' => $user->isAdmin(),
        ]);
    }

    private function greetingForHour(int $hour): string
    {
        if ($hour >= 0 && $hour <= 9) {
            return 'Good Morning,';
        }

        if ($hour >= 10 && $hour <= 11) {
            return 'Good Day,';
        }

        if ($hour >= 12 && $hour <= 15) {
            return 'Good Afternoon,';
        }

        if ($hour >= 16 && $hour <= 23) {
            return 'Good Evening,';
        }

        return 'Welcome,';
    }

    private function buildSearchShortcuts($user)
    {
        $items = collect([
            [
                'label' => 'Dashboard',
                'url' => $user->isAdmin() ? url('/admin') : route('em/dashboard'),
                'keywords' => ['dashboard', 'home'],
            ],
            [
                'label' => $user->isAdmin() ? 'Attendance (Admin)' : 'My Attendance',
                'url' => $user->isAdmin() ? url('/admin/attendance-records') : route('attendance/employee/page'),
                'keywords' => ['attendance', 'check in', 'check out', 'clock in', 'clock out'],
            ],
            [
                'label' => $user->isAdmin() ? 'Leave Requests' : 'My Leaves',
                'url' => $user->isAdmin() ? url('/admin/leaves-admins') : route('form/leavesemployee/new'),
                'keywords' => ['leave', 'vacation', 'time off'],
            ],
            [
                'label' => 'My Payslips',
                'url' => route('my/payslips'),
                'keywords' => ['payslip', 'salary', 'payroll'],
            ],
            [
                'label' => 'My Profile',
                'url' => route('profile_user'),
                'keywords' => ['profile', 'account'],
            ],
        ]);

        if ($user->isAdmin()) {
            $items = $items->merge([
                [
                    'label' => 'Employees',
                    'url' => url('/admin/employees'),
                    'keywords' => ['employee', 'employees', 'staff', 'team'],
                ],
                [
                    'label' => 'Company Settings',
                    'url' => url('/admin/company-settings'),
                    'keywords' => ['settings', 'configuration', 'company settings'],
                ],
                [
                    'label' => 'Payroll',
                    'url' => url('/admin/staff-salaries'),
                    'keywords' => ['payroll', 'salary setup'],
                ],
            ]);
        }

        return $items;
    }
}
