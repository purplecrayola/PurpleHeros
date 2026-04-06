@extends('layouts.master')
@section('content')
    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">{{ $greeting }} {{ Session::get('name') }}</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item active">Dashboard</li>
                        </ul>
                        <p class="text-muted mb-0">{{ $todayLabel }}. Purple HR SMB v1 is focused on core HR operations: people, leave, attendance, payroll, and settings.</p>
                    </div>
                    <div class="col-auto">
                        <a href="{{ url('/admin/employees') }}" class="btn btn-primary">Open Employee Directory</a>
                    </div>
                </div>
            </div>

            {!! Toastr::message() !!}

            <div class="row">
                <div class="col-md-6 col-xl-3 d-flex">
                    <div class="card dash-widget flex-fill">
                        <div class="card-body">
                            <span class="dash-widget-icon"><i class="fa fa-users"></i></span>
                            <div class="dash-widget-info">
                                <h3>{{ $metrics['employees'] }}</h3>
                                <span>Employees</span>
                                <p class="text-muted mb-0 mt-2">{{ $metrics['departments'] }} departments and {{ $metrics['designations'] }} designations configured</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3 d-flex">
                    <div class="card dash-widget flex-fill">
                        <div class="card-body">
                            <span class="dash-widget-icon"><i class="fa fa-calendar"></i></span>
                            <div class="dash-widget-info">
                                <h3>{{ $metrics['leave_requests'] }}</h3>
                                <span>Leave Requests</span>
                                <p class="text-muted mb-0 mt-2">Current recorded leave entries across the organization</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3 d-flex">
                    <div class="card dash-widget flex-fill">
                        <div class="card-body">
                            <span class="dash-widget-icon"><i class="fa fa-clock-o"></i></span>
                            <div class="dash-widget-info">
                                <h3>{{ $metrics['attendance_today'] }}</h3>
                                <span>Attendance Today</span>
                                <p class="text-muted mb-0 mt-2">{{ number_format($metrics['timesheet_hours_week'], 1) }} worked hours logged this week</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-xl-3 d-flex">
                    <div class="card dash-widget flex-fill">
                        <div class="card-body">
                            <span class="dash-widget-icon"><i class="fa fa-money"></i></span>
                            <div class="dash-widget-info">
                                <h3>${{ number_format($metrics['monthly_payroll'], 2) }}</h3>
                                <span>Payroll Register</span>
                                <p class="text-muted mb-0 mt-2">{{ $metrics['pending_overtime'] }} overtime entries pending approval</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8 d-flex">
                    <div class="card flex-fill">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Core Module Shortcuts</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 col-xl-4 mb-3">
                                    <a href="{{ url('/admin/employees') }}" class="btn btn-outline-primary btn-block">Employees</a>
                                </div>
                                <div class="col-md-6 col-xl-4 mb-3">
                                    <a href="{{ url('/admin/leaves-admins') }}" class="btn btn-outline-primary btn-block">Leave</a>
                                </div>
                                <div class="col-md-6 col-xl-4 mb-3">
                                    <a href="{{ url('/admin/attendance-records') }}" class="btn btn-outline-primary btn-block">Attendance</a>
                                </div>
                                <div class="col-md-6 col-xl-4 mb-3">
                                    <a href="{{ url('/admin/timesheet-entries') }}" class="btn btn-outline-primary btn-block">Timesheets</a>
                                </div>
                                <div class="col-md-6 col-xl-4 mb-3">
                                    <a href="{{ url('/admin/overtime-entries') }}" class="btn btn-outline-primary btn-block">Overtime</a>
                                </div>
                                <div class="col-md-6 col-xl-4 mb-3">
                                    <a href="{{ url('/admin/staff-salaries') }}" class="btn btn-outline-primary btn-block">Payroll</a>
                                </div>
                                <div class="col-md-6 col-xl-4 mb-3">
                                    <a href="{{ url('/admin/company-settings') }}" class="btn btn-outline-primary btn-block">Company Settings</a>
                                </div>
                                <div class="col-md-6 col-xl-4 mb-3">
                                    <a href="{{ url('/admin/roles-permissions') }}" class="btn btn-outline-primary btn-block">Roles</a>
                                </div>
                                <div class="col-md-6 col-xl-4 mb-3">
                                    <a href="{{ url('/admin/reports-hub') }}" class="btn btn-outline-primary btn-block">Reports</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 d-flex">
                    <div class="card flex-fill">
                        <div class="card-header">
                            <h4 class="card-title mb-0">SMB v1 Focus</h4>
                        </div>
                        <div class="card-body">
                            <p class="mb-3">This product surface is intentionally narrow so the shipped modules stay stable and demo-ready.</p>
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item px-0">Employees and organization structure</li>
                                <li class="list-group-item px-0">Leave and attendance operations</li>
                                <li class="list-group-item px-0">Timesheets and overtime tracking</li>
                                <li class="list-group-item px-0">Payroll records and payslips</li>
                                <li class="list-group-item px-0">Company settings and role catalog</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6 d-flex">
                    <div class="card flex-fill">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Recent Leave Activity</h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped mb-0">
                                    <thead>
                                        <tr>
                                            <th>Employee</th>
                                            <th>Type</th>
                                            <th>Dates</th>
                                            <th>Days</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($recentLeaves as $leave)
                                            <tr>
                                                <td>{{ $leave->employee_name }}</td>
                                                <td>{{ $leave->leave_type }}</td>
                                                <td>{{ \Carbon\Carbon::parse($leave->from_date)->format('d M Y') }} to {{ \Carbon\Carbon::parse($leave->to_date)->format('d M Y') }}</td>
                                                <td>{{ $leave->day }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="pc-empty-state">No leave records yet.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 d-flex">
                    <div class="card flex-fill">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Recent Attendance</h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped mb-0">
                                    <thead>
                                        <tr>
                                            <th>Employee</th>
                                            <th>Date</th>
                                            <th>Status</th>
                                            <th>Check In</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($recentAttendance as $attendance)
                                            <tr>
                                                <td>{{ $attendance->employee_name }}</td>
                                                <td>{{ \Carbon\Carbon::parse($attendance->attendance_date)->format('d M Y') }}</td>
                                                <td>{{ ucfirst($attendance->status) }}</td>
                                                <td>{{ $attendance->check_in ?: 'N/A' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="pc-empty-state">No attendance records yet.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6 d-flex">
                    <div class="card flex-fill">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Recent Timesheets</h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped mb-0">
                                    <thead>
                                        <tr>
                                            <th>Employee</th>
                                            <th>Work Date</th>
                                            <th>Project</th>
                                            <th>Hours</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($recentTimesheets as $timesheet)
                                            <tr>
                                                <td>{{ $timesheet->employee_name }}</td>
                                                <td>{{ \Carbon\Carbon::parse($timesheet->work_date)->format('d M Y') }}</td>
                                                <td>{{ $timesheet->project_name }}</td>
                                                <td>{{ number_format((float) $timesheet->worked_hours, 1) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="pc-empty-state">No timesheet records yet.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 d-flex">
                    <div class="card flex-fill">
                        <div class="card-header">
                            <h4 class="card-title mb-0">Recent Payroll Records</h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped mb-0">
                                    <thead>
                                        <tr>
                                            <th>Employee</th>
                                            <th>Employee ID</th>
                                            <th>Salary</th>
                                            <th>Updated</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($recentPayroll as $salary)
                                            <tr>
                                                <td>{{ $salary->employee_name }}</td>
                                                <td>{{ $salary->user_id }}</td>
                                                <td>${{ number_format((float) $salary->salary, 2) }}</td>
                                                <td>{{ optional($salary->updated_at)->format ? $salary->updated_at->format('d M Y') : \Carbon\Carbon::parse($salary->updated_at)->format('d M Y') }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="pc-empty-state">No payroll records yet.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
