<?php

namespace App\Http\Controllers;

use App\Models\AttendanceRecord;
use App\Models\LeavePolicyBand;
use App\Models\LeavesAdmin;
use App\Support\InAppNotifier;
use Brian2694\Toastr\Facades\Toastr;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class LeavesController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            $user = Auth::user();
            $method = $request->route()?->getActionMethod();
            $employeeSelfServiceMethods = ['AttendanceEmployee', 'leavesEmployee', 'attendanceCheckIn', 'attendanceCheckOut'];

            if (in_array($method, $employeeSelfServiceMethods, true)) {
                abort_unless($user !== null, 403);
                return $next($request);
            }

            if (in_array($method, ['saveRecord', 'editRecordLeave', 'deleteLeave'], true)) {
                abort_unless($user !== null, 403);
                return $next($request);
            }

            abort_unless($user && $user->isAdmin(), 403);

            return $next($request);
        });
    }

    /** leaves page */
    public function leaves()
    {
        $leaves = $this->leaveBaseQuery()->orderByDesc('leaves_admins.created_at')->get();
        $stats = $this->buildLeaveStats($leaves);
        $leaveTypeOptions = $this->activeLeaveTypeOptions();

        return view('employees.leaves', compact('leaves', 'stats', 'leaveTypeOptions'));
    }

    /** save record */
    public function saveRecord(Request $request)
    {
        $this->authorizeLeaveMutation($request);
        $leaveTypeOptions = $this->activeLeaveTypeOptions();
        $request->validate([
            'user_id' => 'required|string|max:255',
            'leave_type' => ['required', 'string', Rule::in(array_keys($leaveTypeOptions))],
            'from_date' => 'required|string|max:255',
            'to_date' => 'required|string|max:255',
            'leave_reason' => 'required|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $days = $this->calculateLeaveDays($request->from_date, $request->to_date);

            if ($days < 1) {
                DB::rollBack();
                Toastr::error('Leave end date must be after or equal to start date :)', 'Error');
                return redirect()->back();
            }

            if (LeavesAdmin::hasOverlappingRange($request->user_id, $request->from_date, $request->to_date)) {
                DB::rollBack();
                Toastr::error('Leave dates overlap with an existing request for this user :)', 'Error');
                return redirect()->back();
            }

            if ($this->hasDuplicateLeave($request->user_id, $request->leave_type, $request->from_date, $request->to_date)) {
                DB::rollBack();
                Toastr::error('Duplicate leave request detected for this user and date range :)', 'Error');
                return redirect()->back();
            }

            $this->assertLeaveRequestWithinPolicy(
                (string) $request->user_id,
                (string) $request->leave_type,
                (string) $request->from_date,
                (string) $request->to_date,
            );

            $leave = LeavesAdmin::create([
                'user_id' => $request->user_id,
                'leave_type' => $request->leave_type,
                'from_date' => $request->from_date,
                'to_date' => $request->to_date,
                'day' => $days,
                'leave_reason' => $request->leave_reason,
            ]);

            InAppNotifier::notifyUserId(
                (string) $leave->user_id,
                'Leave request submitted',
                sprintf('%s leave request from %s to %s has been submitted.', (string) $leave->leave_type, (string) $leave->from_date, (string) $leave->to_date),
                route('form/leavesemployee/new'),
                'info'
            );

            InAppNotifier::notifyRoles(
                ['Super Admin', 'Admin', 'HR Manager'],
                'New leave request',
                sprintf('A leave request (%s) has been submitted for review.', (string) $leave->leave_type),
                url('/admin/leaves-admins'),
                'pending',
                [(string) $leave->user_id]
            );

            DB::commit();
            Toastr::success('Create new Leaves successfully :)', 'Success');
            return redirect()->back();
        } catch (ValidationException $e) {
            DB::rollBack();
            Toastr::error(collect($e->errors())->flatten()->first() ?: 'Leave request exceeds configured policy limits :)', 'Error');
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Throwable $e) {
            DB::rollBack();
            Toastr::error('Add Leaves fail :)', 'Error');
            return redirect()->back();
        }
    }

    /** edit record */
    public function editRecordLeave(Request $request)
    {
        $this->authorizeLeaveMutation($request);
        $leaveTypeOptions = $this->activeLeaveTypeOptions();
        $request->validate([
            'id' => 'required|integer',
            'leave_type' => ['required', 'string', Rule::in(array_keys($leaveTypeOptions))],
            'from_date' => 'required|string|max:255',
            'to_date' => 'required|string|max:255',
            'leave_reason' => 'required|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $days = $this->calculateLeaveDays($request->from_date, $request->to_date);

            if ($days < 1) {
                DB::rollBack();
                Toastr::error('Leave end date must be after or equal to start date :)', 'Error');
                return redirect()->back();
            }

            $targetUserId = LeavesAdmin::query()->where('id', $request->id)->value('user_id');
            if ($targetUserId && LeavesAdmin::hasOverlappingRange($targetUserId, $request->from_date, $request->to_date, (int) $request->id)) {
                DB::rollBack();
                Toastr::error('Leave dates overlap with an existing request for this user :)', 'Error');
                return redirect()->back();
            }

            if ($targetUserId && $this->hasDuplicateLeave($targetUserId, $request->leave_type, $request->from_date, $request->to_date, (int) $request->id)) {
                DB::rollBack();
                Toastr::error('Duplicate leave request detected for this user and date range :)', 'Error');
                return redirect()->back();
            }

            if ($targetUserId) {
                $this->assertLeaveRequestWithinPolicy(
                    (string) $targetUserId,
                    (string) $request->leave_type,
                    (string) $request->from_date,
                    (string) $request->to_date,
                    (int) $request->id,
                );
            }

            LeavesAdmin::where('id', $request->id)->update([
                'leave_type' => $request->leave_type,
                'from_date' => $request->from_date,
                'to_date' => $request->to_date,
                'day' => $days,
                'leave_reason' => $request->leave_reason,
            ]);

            DB::commit();
            Toastr::success('Updated Leaves successfully :)', 'Success');
            return redirect()->back();
        } catch (ValidationException $e) {
            DB::rollBack();
            Toastr::error(collect($e->errors())->flatten()->first() ?: 'Leave request exceeds configured policy limits :)', 'Error');
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Throwable $e) {
            DB::rollBack();
            Toastr::error('Update Leaves fail :)', 'Error');
            return redirect()->back();
        }
    }

    /** delete record */
    public function deleteLeave(Request $request)
    {
        $this->authorizeLeaveMutation($request);
        try {
            LeavesAdmin::destroy($request->id);
            Toastr::success('Leaves admin deleted successfully :)', 'Success');
            return redirect()->back();
        } catch (\Throwable $e) {
            DB::rollBack();
            Toastr::error('Leaves admin delete fail :)', 'Error');
            return redirect()->back();
        }
    }

    /** leaveSettings page */
    public function leaveSettings()
    {
        return view('employees.leavesettings');
    }

    /** attendance admin */
    public function attendanceIndex(Request $request)
    {
        $filters = [
            'name' => trim((string) $request->get('name', '')),
            'month' => (int) $request->get('month', now()->month),
            'year' => (int) $request->get('year', now()->year),
        ];

        if ($filters['month'] < 1 || $filters['month'] > 12) {
            $filters['month'] = now()->month;
        }

        if ($filters['year'] < 2000 || $filters['year'] > 2100) {
            $filters['year'] = now()->year;
        }

        $records = $this->attendanceBaseQuery()
            ->when($filters['name'] !== '', function ($query) use ($filters) {
                $query->where('users.name', 'like', '%' . $filters['name'] . '%');
            })
            ->whereYear('attendance_records.attendance_date', $filters['year'])
            ->whereMonth('attendance_records.attendance_date', $filters['month'])
            ->orderByDesc('attendance_records.attendance_date')
            ->orderBy('users.name')
            ->get();

        $summary = $this->buildAttendanceSummary($records);
        $employees = $this->buildAttendanceEmployeeSummaries($records);
        $months = $this->monthOptions();
        $years = range(now()->year - 2, now()->year + 1);

        return view('employees.attendance', compact('records', 'summary', 'employees', 'filters', 'months', 'years'));
    }

    /** attendance employee */
    public function AttendanceEmployee(Request $request)
    {
        $filters = [
            'month' => (int) $request->get('month', now()->month),
            'year' => (int) $request->get('year', now()->year),
        ];

        if ($filters['month'] < 1 || $filters['month'] > 12) {
            $filters['month'] = now()->month;
        }

        if ($filters['year'] < 2000 || $filters['year'] > 2100) {
            $filters['year'] = now()->year;
        }

        $records = $this->attendanceBaseQuery()
            ->where('attendance_records.user_id', Auth::user()->user_id)
            ->whereYear('attendance_records.attendance_date', $filters['year'])
            ->whereMonth('attendance_records.attendance_date', $filters['month'])
            ->orderByDesc('attendance_records.attendance_date')
            ->get();

        $summary = $this->buildAttendanceSummary($records);
        $latestRecord = $records->first();
        $activity = $records->take(5);
        $todayRecord = AttendanceRecord::query()
            ->where('user_id', Auth::user()->user_id)
            ->whereDate('attendance_date', Carbon::today()->toDateString())
            ->first();
        $canCheckIn = $todayRecord === null || $todayRecord->check_in === null;
        $canCheckOut = $todayRecord !== null && $todayRecord->check_in !== null && $todayRecord->check_out === null;
        $months = $this->monthOptions();
        $years = range(now()->year - 2, now()->year + 1);

        return view('employees.attendanceemployee', compact(
            'records',
            'summary',
            'latestRecord',
            'activity',
            'filters',
            'months',
            'years',
            'todayRecord',
            'canCheckIn',
            'canCheckOut'
        ));
    }

    public function attendanceCheckIn(Request $request)
    {
        $user = Auth::user();
        abort_unless($user !== null, 403);

        $today = Carbon::today();
        $now = Carbon::now();

        $record = AttendanceRecord::query()->firstOrNew([
            'user_id' => $user->user_id,
            'attendance_date' => $today->toDateString(),
        ]);

        if ($record->exists && $record->check_in !== null) {
            Toastr::info('You already checked in today at ' . Carbon::parse($record->check_in)->format('h:i A') . '.', 'Info');
            return redirect()->route('attendance/employee/page');
        }

        if (! $record->exists) {
            $record->status = 'Present';
            $record->work_minutes = 0;
            $record->break_minutes = 0;
            $record->overtime_minutes = 0;
        } elseif ($record->status === 'Absent') {
            $record->status = 'Present';
        }

        $record->check_in = $now->format('H:i:s');
        $record->save();

        Toastr::success('Check-in recorded at ' . $now->format('h:i A') . '.', 'Success');
        return redirect()->route('attendance/employee/page');
    }

    public function attendanceCheckOut(Request $request)
    {
        $user = Auth::user();
        abort_unless($user !== null, 403);

        $today = Carbon::today();
        $now = Carbon::now();

        $record = AttendanceRecord::query()
            ->where('user_id', $user->user_id)
            ->whereDate('attendance_date', $today->toDateString())
            ->first();

        if ($record === null || $record->check_in === null) {
            Toastr::error('Please check in first before checking out.', 'Error');
            return redirect()->route('attendance/employee/page');
        }

        if ($record->check_out !== null) {
            Toastr::info('You already checked out today at ' . Carbon::parse($record->check_out)->format('h:i A') . '.', 'Info');
            return redirect()->route('attendance/employee/page');
        }

        $checkInDateTime = Carbon::parse($today->toDateString() . ' ' . $record->check_in);
        $record->check_out = $now->format('H:i:s');
        $record->work_minutes = max(0, $checkInDateTime->diffInMinutes($now));
        if ($record->status === 'Absent') {
            $record->status = 'Present';
        }
        $record->save();

        Toastr::success('Check-out recorded at ' . $now->format('h:i A') . '.', 'Success');
        return redirect()->route('attendance/employee/page');
    }

    /** leaves Employee */
    public function leavesEmployee()
    {
        $leaves = $this->leaveBaseQuery()
            ->where('leaves_admins.user_id', Auth::user()->user_id)
            ->orderByDesc('leaves_admins.created_at')
            ->get();
        $stats = $this->buildLeaveStats($leaves);
        $leaveTypeOptions = $this->activeLeaveTypeOptions();

        return view('employees.leavesemployee', compact('leaves', 'stats', 'leaveTypeOptions'));
    }

    /** shift scheduling */
    public function shiftScheduLing()
    {
        return view('employees.shiftscheduling');
    }

    /** shiftList */
    public function shiftList()
    {
        return view('employees.shiftlist');
    }


    private function authorizeLeaveMutation(Request $request): void
    {
        $user = Auth::user();

        abort_unless($user !== null, 403);

        if ($user->isAdmin()) {
            return;
        }

        $targetUserId = $request->input('user_id');

        if ($targetUserId === null && $request->filled('id')) {
            $targetUserId = LeavesAdmin::query()->where('id', $request->input('id'))->value('user_id');
        }

        abort_unless($user->canAccessUserId($targetUserId), 403);
    }

    private function leaveBaseQuery()
    {
        return DB::table('leaves_admins')
            ->join('users', 'users.user_id', '=', 'leaves_admins.user_id')
            ->select('leaves_admins.*', 'users.position', 'users.name', 'users.avatar');
    }

    private function attendanceBaseQuery()
    {
        return DB::table('attendance_records')
            ->join('users', 'users.user_id', '=', 'attendance_records.user_id')
            ->select(
                'attendance_records.*',
                'users.name',
                'users.avatar',
                'users.position',
                'users.department'
            );
    }

    private function calculateLeaveDays(string $fromDate, string $toDate): int
    {
        $from = new DateTime($fromDate);
        $to = new DateTime($toDate);

        if ($to < $from) {
            return 0;
        }

        return $from->diff($to)->days + 1;
    }

    private function hasDuplicateLeave(string $userId, string $leaveType, string $fromDate, string $toDate, ?int $ignoreId = null): bool
    {
        $signature = LeavesAdmin::buildSignature($userId, $leaveType, $fromDate, $toDate);

        return LeavesAdmin::query()
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->where('leave_signature', $signature)
            ->exists();
    }

    private function buildLeaveStats($leaves): array
    {
        $annual = 0;
        $sick = 0;
        $maternity = 0;
        $unpaid = 0;
        $other = 0;
        $policyMap = LeavePolicyBand::activeNameMap();
        $annualAllowance = 0;
        foreach ($policyMap as $band) {
            if (($band->category ?? 'other') !== 'annual') {
                continue;
            }

            $annualAllowance += (int) ($band->annual_entitlement_days ?? 0);
            if ((bool) ($band->carry_forward_enabled ?? false)) {
                $annualAllowance += (int) ($band->carry_forward_cap_days ?? 0);
            }
        }

        foreach ($leaves as $leave) {
            $days = (int) $leave->day;
            $normalized = mb_strtolower(trim((string) $leave->leave_type));
            $category = $policyMap[$normalized]->category ?? 'other';

            if ($category === 'annual') {
                $annual += $days;
            } elseif ($category === 'sick') {
                $sick += $days;
            } elseif ($category === 'maternity') {
                $maternity += $days;
            } elseif ($category === 'unpaid') {
                $unpaid += $days;
            } else {
                $other += $days;
            }
        }

        return [
            'annual' => $annual,
            'sick' => $sick,
            'maternity' => $maternity,
            'unpaid' => $unpaid,
            'other' => $other,
            'remaining' => max($annualAllowance - $annual, 0),
            'total' => $annual + $sick + $maternity + $unpaid + $other,
        ];
    }

    private function activeLeaveTypeOptions(): array
    {
        return LeavePolicyBand::activeOptions();
    }

    private function assertLeaveRequestWithinPolicy(
        string $userId,
        string $leaveType,
        string $fromDate,
        string $toDate,
        ?int $ignoreLeaveId = null
    ): void {
        $policyMap = LeavePolicyBand::activeNameMap();
        $band = $policyMap[mb_strtolower(trim($leaveType))] ?? null;

        if (! $band) {
            throw ValidationException::withMessages([
                'leave_type' => 'Selected leave type is not active in leave policy settings.',
            ]);
        }

        if (($band->annual_entitlement_days ?? null) === null) {
            return;
        }

        $requestedDays = $this->calculateLeaveDays($fromDate, $toDate);
        $year = Carbon::parse($fromDate)->year;
        $allowance = (int) ($band->annual_entitlement_days ?? 0);
        if ((bool) ($band->carry_forward_enabled ?? false)) {
            $allowance += (int) ($band->carry_forward_cap_days ?? 0);
        }

        $usedDays = (int) LeavesAdmin::query()
            ->when($ignoreLeaveId, fn ($query) => $query->where('id', '!=', $ignoreLeaveId))
            ->where('user_id', $userId)
            ->where('leave_type', $leaveType)
            ->whereYear('from_date', $year)
            ->where('status', '!=', 'Rejected')
            ->sum(DB::raw('COALESCE(day, 0)'));

        if (($usedDays + $requestedDays) > $allowance) {
            throw ValidationException::withMessages([
                'leave_type' => "Leave request exceeds policy limit for {$leaveType}. Entitlement: {$allowance} days, already used: {$usedDays} days.",
            ]);
        }
    }

    private function buildAttendanceSummary($records): array
    {
        $presentDays = $records->where('status', 'Present')->count();
        $remoteDays = $records->where('status', 'Remote')->count();
        $absentDays = $records->where('status', 'Absent')->count();
        $lateDays = $records->where('status', 'Late')->count();
        $workMinutes = $records->sum('work_minutes');
        $overtimeMinutes = $records->sum('overtime_minutes');
        $breakMinutes = $records->sum('break_minutes');

        return [
            'records' => $records->count(),
            'present' => $presentDays,
            'remote' => $remoteDays,
            'absent' => $absentDays,
            'late' => $lateDays,
            'work_hours' => round($workMinutes / 60, 1),
            'overtime_hours' => round($overtimeMinutes / 60, 1),
            'break_hours' => round($breakMinutes / 60, 1),
        ];
    }

    private function buildAttendanceEmployeeSummaries($records)
    {
        return $records
            ->groupBy('user_id')
            ->map(function ($employeeRecords) {
                $first = $employeeRecords->first();

                return [
                    'user_id' => $first->user_id,
                    'name' => $first->name,
                    'position' => $first->position,
                    'department' => $first->department,
                    'avatar' => $first->avatar,
                    'days_logged' => $employeeRecords->count(),
                    'work_hours' => round($employeeRecords->sum('work_minutes') / 60, 1),
                    'overtime_hours' => round($employeeRecords->sum('overtime_minutes') / 60, 1),
                    'late_days' => $employeeRecords->where('status', 'Late')->count(),
                    'absent_days' => $employeeRecords->where('status', 'Absent')->count(),
                ];
            })
            ->sortBy('name')
            ->values();
    }

    private function monthOptions(): array
    {
        return collect(range(1, 12))
            ->mapWithKeys(fn ($month) => [$month => Carbon::create()->month($month)->format('F')])
            ->all();
    }
}
