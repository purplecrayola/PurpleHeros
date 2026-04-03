<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use App\Models\AttendanceRecord;
use App\Models\BankInformation;
use App\Models\Employee;
use App\Models\EmployeeStatutoryProfile;
use App\Models\LeavesAdmin;
use App\Models\OvertimeEntry;
use App\Models\PersonalInformation;
use App\Models\ProfileInformation;
use App\Models\StaffSalary;
use App\Models\TimesheetEntry;
use App\Models\User;
use App\Models\UserEmergencyContact;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Artisan::command('admin:ensure {--email=admin@purplecrayola.com} {--password=Password123!} {--role=Super Admin}', function () {
    $email = (string) $this->option('email');
    $password = (string) $this->option('password');
    $role = (string) $this->option('role');
    $now = now()->format('Y-m-d H:i:s');

    $user = User::query()->updateOrCreate(
        ['email' => $email],
        [
            'name' => 'Purple HR Admin',
            'password' => Hash::make($password),
            'role_name' => $role,
            'status' => 'Active',
            'join_date' => $now,
            'last_login' => $now,
            'phone_number' => '+2348000000000',
            'position' => 'HR Lead',
            'department' => 'People Operations',
        ]
    );

    $this->info("Admin account ready: {$user->email} ({$user->role_name})");
})->purpose('Create or reset the local admin account for Filament login');

Artisan::command('rebuild:core-parity
    {--sync : Backfill missing employee/payroll records from users}
    {--seed-attendance : Seed starter attendance records for users}
    {--seed-timesheets : Seed starter timesheet entries for users}
    {--seed-overtime : Seed starter overtime entries for users}
    {--seed-leaves : Seed starter leave requests for users}', function () {
    $userIds = User::query()->pluck('user_id')->filter()->values();

    $orphanEmployees = Employee::query()
        ->whereNotNull('employee_id')
        ->whereNotIn('employee_id', $userIds)
        ->count();

    $orphanSalaries = StaffSalary::query()
        ->whereNotNull('user_id')
        ->whereNotIn('user_id', $userIds)
        ->count();

    $orphanLeaves = LeavesAdmin::query()
        ->whereNotNull('user_id')
        ->whereNotIn('user_id', $userIds)
        ->count();

    $orphanAttendance = AttendanceRecord::query()
        ->whereNotNull('user_id')
        ->whereNotIn('user_id', $userIds)
        ->count();

    $orphanTimesheets = TimesheetEntry::query()
        ->whereNotNull('user_id')
        ->whereNotIn('user_id', $userIds)
        ->count();

    $orphanOvertime = OvertimeEntry::query()
        ->whereNotNull('user_id')
        ->whereNotIn('user_id', $userIds)
        ->count();

    $createdEmployees = 0;
    $createdPayrollRecords = 0;
    $updatedPayrollNames = 0;
    $createdAttendanceRecords = 0;
    $createdTimesheetEntries = 0;
    $createdOvertimeEntries = 0;
    $createdLeaveRequests = 0;

    if ((bool) $this->option('sync')) {
        User::query()->orderBy('id')->chunkById(200, function ($users) use (&$createdEmployees, &$createdPayrollRecords, &$updatedPayrollNames) {
            foreach ($users as $user) {
                if (! $user->user_id) {
                    continue;
                }

                $employeeExists = Employee::query()->where('employee_id', $user->user_id)->exists();
                if (! $employeeExists) {
                    Employee::query()->create([
                        'name' => $user->name,
                        'email' => $user->email,
                        'birth_date' => null,
                        'gender' => null,
                        'employee_id' => $user->user_id,
                        'company' => 'Purple HR',
                    ]);
                    $createdEmployees++;
                }

                $salary = StaffSalary::query()->where('user_id', $user->user_id)->first();
                if (! $salary) {
                    StaffSalary::query()->create([
                        'name' => $user->name,
                        'user_id' => $user->user_id,
                        'salary' => '0',
                        'basic' => '0',
                        'da' => '0',
                        'hra' => '0',
                        'conveyance' => '0',
                        'allowance' => '0',
                        'medical_allowance' => '0',
                        'tds' => '0',
                        'esi' => '0',
                        'pf' => '0',
                        'leave' => '0',
                        'prof_tax' => '0',
                        'labour_welfare' => '0',
                    ]);
                    $createdPayrollRecords++;
                } elseif (! $salary->name || trim((string) $salary->name) === '') {
                    $salary->update(['name' => $user->name]);
                    $updatedPayrollNames++;
                }
            }
        });
    }

    $seedAttendance = (bool) $this->option('seed-attendance');
    $seedTimesheets = (bool) $this->option('seed-timesheets');
    $seedOvertime = (bool) $this->option('seed-overtime');
    $seedLeaves = (bool) $this->option('seed-leaves');

    if ($seedAttendance || $seedTimesheets || $seedOvertime || $seedLeaves) {
        User::query()->orderBy('id')->chunkById(200, function ($users) use (
            $seedAttendance,
            $seedTimesheets,
            $seedOvertime,
            $seedLeaves,
            &$createdAttendanceRecords,
            &$createdTimesheetEntries,
            &$createdOvertimeEntries,
            &$createdLeaveRequests
        ) {
            foreach ($users as $user) {
                if (! $user->user_id) {
                    continue;
                }

                if ($seedAttendance) {
                    $attendanceDate = now()->toDateString();
                    $attendance = AttendanceRecord::query()->firstOrCreate(
                        [
                            'user_id' => $user->user_id,
                            'attendance_date' => $attendanceDate,
                        ],
                        [
                            'status' => 'Present',
                            'check_in' => '09:00:00',
                            'check_out' => '17:00:00',
                            'work_minutes' => 480,
                            'break_minutes' => 60,
                            'overtime_minutes' => 0,
                            'notes' => 'Starter record generated by rebuild:core-parity.',
                        ]
                    );

                    if ($attendance->wasRecentlyCreated) {
                        $createdAttendanceRecords++;
                    }
                }

                if ($seedTimesheets) {
                    $workDate = now()->subDay()->toDateString();
                    $timesheet = TimesheetEntry::query()->firstOrCreate(
                        [
                            'user_id' => $user->user_id,
                            'work_date' => $workDate,
                            'project_name' => 'HR Operations',
                        ],
                        [
                            'assigned_hours' => 8,
                            'worked_hours' => 8,
                            'description' => 'Starter timesheet entry generated by rebuild:core-parity.',
                        ]
                    );

                    if ($timesheet->wasRecentlyCreated) {
                        $createdTimesheetEntries++;
                    }
                }

                if ($seedOvertime) {
                    $otDate = now()->subDays(2)->toDateString();
                    $overtime = OvertimeEntry::query()->firstOrCreate(
                        [
                            'user_id' => $user->user_id,
                            'ot_date' => $otDate,
                            'ot_type' => 'Planned',
                        ],
                        [
                            'hours' => 1.50,
                            'status' => 'Pending',
                            'approved_by' => null,
                            'description' => 'Starter overtime entry generated by rebuild:core-parity.',
                        ]
                    );

                    if ($overtime->wasRecentlyCreated) {
                        $createdOvertimeEntries++;
                    }
                }

                if ($seedLeaves) {
                    $fromDate = now()->addDays(3)->toDateString();
                    $toDate = now()->addDays(4)->toDateString();
                    $leave = LeavesAdmin::query()->firstOrCreate(
                        [
                            'user_id' => $user->user_id,
                            'leave_type' => 'Casual Leave',
                            'from_date' => $fromDate,
                            'to_date' => $toDate,
                        ],
                        [
                            'day' => '2',
                            'leave_reason' => 'Starter leave request generated by rebuild:core-parity.',
                        ]
                    );

                    if ($leave->wasRecentlyCreated) {
                        $createdLeaveRequests++;
                    }
                }
            }
        });
    }

    $this->table(
        ['Metric', 'Count'],
        [
            ['Users', User::query()->count()],
            ['Employees', Employee::query()->count()],
            ['Staff Salaries', StaffSalary::query()->count()],
            ['Leave Requests', LeavesAdmin::query()->count()],
            ['Attendance Records', AttendanceRecord::query()->count()],
            ['Timesheet Entries', TimesheetEntry::query()->count()],
            ['Overtime Entries', OvertimeEntry::query()->count()],
            ['Orphan Employees (employee_id missing in users)', $orphanEmployees],
            ['Orphan Payroll (user_id missing in users)', $orphanSalaries],
            ['Orphan Leaves (user_id missing in users)', $orphanLeaves],
            ['Orphan Attendance (user_id missing in users)', $orphanAttendance],
            ['Orphan Timesheets (user_id missing in users)', $orphanTimesheets],
            ['Orphan Overtime (user_id missing in users)', $orphanOvertime],
        ]
    );

    if ((bool) $this->option('sync')) {
        $this->newLine();
        $this->table(
            ['Sync Action', 'Affected'],
            [
                ['Employees created from users', $createdEmployees],
                ['Payroll records created from users', $createdPayrollRecords],
                ['Payroll names updated', $updatedPayrollNames],
            ]
        );
    } else {
        $this->newLine();
        $this->comment('Dry run mode: no records were changed. Re-run with --sync to backfill.');
    }

    if ($seedAttendance || $seedTimesheets || $seedOvertime || $seedLeaves) {
        $this->newLine();
        $this->table(
            ['Sample Data Action', 'Affected'],
            [
                ['Attendance starter records created', $createdAttendanceRecords],
                ['Timesheet starter records created', $createdTimesheetEntries],
                ['Overtime starter records created', $createdOvertimeEntries],
                ['Leave starter records created', $createdLeaveRequests],
            ]
        );
    }
})->purpose('Audit rebuild parity for core HR modules, with optional user-to-core sync');

Artisan::command('rebuild:uat-users {--password=Password123! : Password to assign to all demo users}', function () {
    $password = (string) $this->option('password');
    $baseTime = now()->subHours(3);

    $profiles = [
        [
            'name' => 'Purple HR Admin',
            'email' => 'admin@purplecrayola.com',
            'role_name' => 'Super Admin',
            'position' => 'HR Lead',
            'department' => 'People Operations',
            'phone_number' => '+2348000000000',
            'salary' => '850000',
        ],
        [
            'name' => 'Amaka Obi',
            'email' => 'hr.manager@purplecrayola.com',
            'role_name' => 'HR Manager',
            'position' => 'HR Manager',
            'department' => 'People Operations',
            'phone_number' => '+2348000000001',
            'salary' => '620000',
        ],
        [
            'name' => 'David Cole',
            'email' => 'payroll.admin@purplecrayola.com',
            'role_name' => 'Payroll Admin',
            'position' => 'Payroll Lead',
            'department' => 'Finance',
            'phone_number' => '+2348000000002',
            'salary' => '580000',
        ],
        [
            'name' => 'Tomi Ade',
            'email' => 'ops.manager@purplecrayola.com',
            'role_name' => 'Operations Manager',
            'position' => 'Operations Manager',
            'department' => 'Operations',
            'phone_number' => '+2348000000003',
            'salary' => '540000',
        ],
        [
            'name' => 'Lara Price',
            'email' => 'reports.analyst@purplecrayola.com',
            'role_name' => 'Reports Analyst',
            'position' => 'People Analyst',
            'department' => 'Strategy',
            'phone_number' => '+2348000000004',
            'salary' => '500000',
        ],
        [
            'name' => 'Daniel Young',
            'email' => 'employee@purplecrayola.com',
            'role_name' => 'Employee',
            'position' => 'Software Engineer',
            'department' => 'Engineering',
            'phone_number' => '+2348000000005',
            'salary' => '420000',
        ],
    ];

    $createdUsers = 0;
    $updatedUsers = 0;
    $scenarioAttendance = 0;
    $scenarioTimesheets = 0;
    $scenarioOvertime = 0;
    $scenarioLeaves = 0;

    $usersByRole = [];

    foreach ($profiles as $index => $profile) {
        $timestamp = $baseTime->copy()->addSeconds($index + 1)->format('Y-m-d H:i:s');

        $existing = User::query()->where('email', $profile['email'])->first();

        $user = User::query()->updateOrCreate(
            ['email' => $profile['email']],
            [
                'name' => $profile['name'],
                'password' => Hash::make($password),
                'role_name' => $profile['role_name'],
                'status' => 'Active',
                'join_date' => $existing?->join_date ?: $timestamp,
                'last_login' => $timestamp,
                'phone_number' => $profile['phone_number'],
                'position' => $profile['position'],
                'department' => $profile['department'],
            ]
        );

        if ($existing) {
            $updatedUsers++;
        } else {
            $createdUsers++;
        }

        $usersByRole[$profile['role_name']] = $user;

        Employee::query()->updateOrCreate(
            ['employee_id' => $user->user_id],
            [
                'name' => $user->name,
                'email' => $user->email,
                'birth_date' => null,
                'gender' => null,
                'company' => 'Purple HR',
            ]
        );

        StaffSalary::query()->updateOrCreate(
            ['user_id' => $user->user_id],
            [
                'name' => $user->name,
                'salary' => $profile['salary'],
                'basic' => (string) round(((float) $profile['salary']) * 0.55, 2),
                'da' => '40000',
                'hra' => '55000',
                'conveyance' => '15000',
                'allowance' => '18000',
                'medical_allowance' => '10000',
                'tds' => '20000',
                'esi' => '2500',
                'pf' => '5000',
                'leave' => '0',
                'prof_tax' => '1500',
                'labour_welfare' => '500',
            ]
        );
    }

    $adminEmail = $usersByRole['Super Admin']->email ?? 'admin@purplecrayola.com';
    $today = now()->toDateString();
    $yesterday = now()->subDay()->toDateString();

    foreach ($usersByRole as $role => $user) {
        $status = match ($role) {
            'Operations Manager' => 'Late',
            'Reports Analyst' => 'Remote',
            'Employee' => 'Present',
            default => 'Present',
        };

        $attendance = AttendanceRecord::query()->firstOrCreate(
            ['user_id' => $user->user_id, 'attendance_date' => $today],
            [
                'status' => $status,
                'check_in' => $status === 'Late' ? '10:05:00' : '09:00:00',
                'check_out' => '17:30:00',
                'work_minutes' => 510,
                'break_minutes' => 45,
                'overtime_minutes' => in_array($role, ['Operations Manager', 'Employee'], true) ? 90 : 30,
                'notes' => "UAT scenario attendance for {$role}.",
            ]
        );
        if ($attendance->wasRecentlyCreated) {
            $scenarioAttendance++;
        }

        $timesheet = TimesheetEntry::query()->firstOrCreate(
            ['user_id' => $user->user_id, 'work_date' => $yesterday, 'project_name' => 'Q2 Workforce Program'],
            [
                'assigned_hours' => 8,
                'worked_hours' => $role === 'Employee' ? 9 : 8,
                'description' => "UAT scenario timesheet for {$role}.",
            ]
        );
        if ($timesheet->wasRecentlyCreated) {
            $scenarioTimesheets++;
        }
    }

    if (isset($usersByRole['Employee'])) {
        $employee = $usersByRole['Employee'];

        $overtimePending = OvertimeEntry::query()->firstOrCreate(
            ['user_id' => $employee->user_id, 'ot_date' => now()->subDays(1)->toDateString(), 'ot_type' => 'Emergency'],
            [
                'hours' => 2.00,
                'status' => 'Pending',
                'description' => 'Customer escalation coverage.',
            ]
        );
        if ($overtimePending->wasRecentlyCreated) {
            $scenarioOvertime++;
        }

        $leavePending = LeavesAdmin::query()->firstOrCreate(
            ['user_id' => $employee->user_id, 'leave_type' => 'Annual Leave', 'from_date' => now()->addDays(5)->toDateString(), 'to_date' => now()->addDays(7)->toDateString()],
            [
                'day' => '3',
                'status' => 'Pending',
                'leave_reason' => 'Family event (UAT scenario).',
            ]
        );
        if ($leavePending->wasRecentlyCreated) {
            $scenarioLeaves++;
        }
    }

    if (isset($usersByRole['Operations Manager'])) {
        $ops = $usersByRole['Operations Manager'];

        $overtimeApproved = OvertimeEntry::query()->firstOrCreate(
            ['user_id' => $ops->user_id, 'ot_date' => now()->subDays(2)->toDateString(), 'ot_type' => 'Planned'],
            [
                'hours' => 1.50,
                'status' => 'Approved',
                'approved_by' => $adminEmail,
                'approved_at' => now()->subDay(),
                'description' => 'Month-end operational close.',
            ]
        );
        if ($overtimeApproved->wasRecentlyCreated) {
            $scenarioOvertime++;
        }
    }

    if (isset($usersByRole['HR Manager'])) {
        $hr = $usersByRole['HR Manager'];

        $leaveApproved = LeavesAdmin::query()->firstOrCreate(
            ['user_id' => $hr->user_id, 'leave_type' => 'Casual Leave', 'from_date' => now()->addDays(10)->toDateString(), 'to_date' => now()->addDays(10)->toDateString()],
            [
                'day' => '1',
                'status' => 'Approved',
                'approved_by' => $adminEmail,
                'approved_at' => now(),
                'leave_reason' => 'Personal appointment (UAT scenario).',
            ]
        );
        if ($leaveApproved->wasRecentlyCreated) {
            $scenarioLeaves++;
        }
    }

    $this->table(
        ['UAT User Bootstrap', 'Count'],
        [
            ['Users created', $createdUsers],
            ['Users updated', $updatedUsers],
            ['Scenario attendance records created', $scenarioAttendance],
            ['Scenario timesheet records created', $scenarioTimesheets],
            ['Scenario overtime records created', $scenarioOvertime],
            ['Scenario leave records created', $scenarioLeaves],
        ]
    );

    $this->newLine();
    $this->info('Default UAT password for all listed users: ' . $password);
})->purpose('Create role-based UAT users and seed role-specific workflow scenarios');

Artisan::command('rebuild:uat-bootstrap', function () {
    $this->info('Running UAT bootstrap for rebuild modules...');

    $this->call('rebuild:uat-users');

    $this->call('rebuild:core-parity', [
        '--sync' => true,
        '--seed-attendance' => true,
        '--seed-timesheets' => true,
        '--seed-overtime' => true,
        '--seed-leaves' => true,
    ]);

    $this->newLine();
    $this->info('UAT bootstrap complete.');
})->purpose('Run full rebuild parity sync and starter seed data for UAT');

Artisan::command('employees:sync-linked-fields {--user_id= : Sync only one employee user_id}', function () {
    $targetUserId = trim((string) $this->option('user_id'));

    $employees = Employee::query()
        ->when($targetUserId !== '', fn ($query) => $query->where('employee_id', $targetUserId))
        ->orderBy('id')
        ->get();

    if ($employees->isEmpty()) {
        $this->warn($targetUserId !== '' ? "No employee found for user_id {$targetUserId}." : 'No employees found.');
        return;
    }

    $synced = 0;

    foreach ($employees as $employee) {
        $userId = (string) ($employee->employee_id ?? '');
        if ($userId === '') {
            continue;
        }

        $user = User::query()->where('user_id', $userId)->first();
        if (! $user) {
            $this->warn("Skipped {$userId}: no matching user.");
            continue;
        }

        $fullName = trim((string) ($employee->name ?: $user->name ?: ''));
        $firstName = trim((string) ($employee->first_name ?: $user->first_name ?: ''));
        $lastName = trim((string) ($employee->last_name ?: $user->last_name ?: ''));
        if ($firstName === '' && $lastName === '' && $fullName !== '') {
            $parts = preg_split('/\s+/', $fullName) ?: [];
            $firstName = (string) ($parts[0] ?? '');
            $lastName = (string) (count($parts) > 1 ? implode(' ', array_slice($parts, 1)) : '');
        }

        $birthDate = $employee->birth_date ? \Illuminate\Support\Carbon::parse($employee->birth_date)->toDateString() : null;

        ProfileInformation::query()->updateOrCreate(
            ['user_id' => $userId],
            [
                'name' => $fullName !== '' ? $fullName : $user->name,
                'first_name' => $firstName !== '' ? $firstName : null,
                'last_name' => $lastName !== '' ? $lastName : null,
                'email' => $employee->email ?: $user->email,
                'birth_date' => $birthDate,
                'gender' => $employee->gender,
                'phone_number' => $user->phone_number,
                'department' => $user->department,
                'designation' => $user->position,
            ]
        );

        User::query()->where('user_id', $userId)->update([
            'name' => $fullName !== '' ? $fullName : $user->name,
            'first_name' => $firstName !== '' ? $firstName : null,
            'last_name' => $lastName !== '' ? $lastName : null,
            'email' => $employee->email ?: $user->email,
        ]);

        $synced++;
    }

    $this->info("Linked-field sync complete. Employees synced: {$synced}.");
})->purpose('Sync admin employee edit core fields into linked profile/user records for consistency');
