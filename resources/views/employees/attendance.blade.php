@extends('layouts.master')
@section('content')
    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">Attendance</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Attendance</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3 col-sm-6">
                    <div class="stats-info">
                        <h6>Attendance Records</h6>
                        <h4>{{ $summary['records'] }} <span>this period</span></h4>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="stats-info">
                        <h6>Present + Remote</h6>
                        <h4>{{ $summary['present'] + $summary['remote'] }} <span>logged days</span></h4>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="stats-info">
                        <h6>Total Work Hours</h6>
                        <h4>{{ $summary['work_hours'] }} <span>hours</span></h4>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="stats-info">
                        <h6>Overtime</h6>
                        <h4>{{ $summary['overtime_hours'] }} <span>hours</span></h4>
                    </div>
                </div>
            </div>

            <div class="row filter-row">
                <div class="col-sm-6 col-md-4">
                    <form action="{{ route('attendance/page') }}" method="GET" class="mb-0">
                        <div class="form-group form-focus">
                            <input type="text" name="name" value="{{ $filters['name'] }}" class="form-control floating">
                            <label class="focus-label">Employee Name</label>
                        </div>
                </div>
                <div class="col-sm-3 col-md-3">
                    <div class="form-group form-focus select-focus">
                        <select class="select floating" name="month">
                            @foreach ($months as $monthValue => $monthLabel)
                                <option value="{{ $monthValue }}" {{ (int) $filters['month'] === (int) $monthValue ? 'selected' : '' }}>{{ $monthLabel }}</option>
                            @endforeach
                        </select>
                        <label class="focus-label">Month</label>
                    </div>
                </div>
                <div class="col-sm-3 col-md-3">
                    <div class="form-group form-focus select-focus">
                        <select class="select floating" name="year">
                            @foreach ($years as $year)
                                <option value="{{ $year }}" {{ (int) $filters['year'] === (int) $year ? 'selected' : '' }}>{{ $year }}</option>
                            @endforeach
                        </select>
                        <label class="focus-label">Year</label>
                    </div>
                </div>
                <div class="col-sm-3 col-md-2">
                    <button type="submit" class="btn btn-success btn-block">Filter</button>
                    </form>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-5">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Team Summary</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped custom-table mb-0">
                                    <thead>
                                        <tr>
                                            <th>Employee</th>
                                            <th>Days</th>
                                            <th>Work Hrs</th>
                                            <th>Late</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($employees as $employee)
                                            @php
                                                $avatar = \App\Support\MediaStorageManager::publicUrl($employee['avatar'] ?? null, 'assets/img/profiles/avatar-01.jpg', 'assets/images');
                                            @endphp
                                            <tr>
                                                <td>
                                                    <h2 class="table-avatar">
                                                        <a href="{{ url('employee/profile/' . $employee['user_id']) }}" class="avatar avatar-xs"><img alt="{{ $employee['name'] }}" src="{{ $avatar }}"></a>
                                                        <a href="{{ url('employee/profile/' . $employee['user_id']) }}">{{ $employee['name'] }} <span>{{ $employee['position'] }}</span></a>
                                                    </h2>
                                                </td>
                                                <td>{{ $employee['days_logged'] }}</td>
                                                <td>{{ $employee['work_hours'] }}</td>
                                                <td>{{ $employee['late_days'] }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center text-muted">No attendance records found for this filter.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-7">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Daily Attendance Log</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped custom-table mb-0 datatable">
                                    <thead>
                                        <tr>
                                            <th>Employee</th>
                                            <th>Date</th>
                                            <th>Status</th>
                                            <th>Check In</th>
                                            <th>Check Out</th>
                                            <th>Work Hrs</th>
                                            <th>Break</th>
                                            <th>OT</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($records as $record)
                                            @php
                                                $avatar = \App\Support\MediaStorageManager::publicUrl($record->avatar ?? null, 'assets/img/profiles/avatar-01.jpg', 'assets/images');
                                                $badgeClass = match ($record->status) {
                                                    'Present' => 'success',
                                                    'Remote' => 'info',
                                                    'Late' => 'warning',
                                                    'Absent' => 'danger',
                                                    default => 'secondary',
                                                };
                                            @endphp
                                            <tr>
                                                <td>
                                                    <h2 class="table-avatar">
                                                        <a href="{{ url('employee/profile/' . $record->user_id) }}" class="avatar avatar-xs"><img alt="{{ $record->name }}" src="{{ $avatar }}"></a>
                                                        <a href="{{ url('employee/profile/' . $record->user_id) }}">{{ $record->name }} <span>{{ $record->department }}</span></a>
                                                    </h2>
                                                </td>
                                                <td>{{ \Illuminate\Support\Carbon::parse($record->attendance_date)->format('d M Y') }}</td>
                                                <td><span class="badge bg-inverse-{{ $badgeClass }}">{{ $record->status }}</span></td>
                                                <td>{{ $record->check_in ? \Illuminate\Support\Carbon::parse($record->check_in)->format('h:i A') : '—' }}</td>
                                                <td>{{ $record->check_out ? \Illuminate\Support\Carbon::parse($record->check_out)->format('h:i A') : '—' }}</td>
                                                <td>{{ number_format($record->work_minutes / 60, 1) }}</td>
                                                <td>{{ number_format($record->break_minutes / 60, 1) }}</td>
                                                <td>{{ number_format($record->overtime_minutes / 60, 1) }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center text-muted">No attendance records found for this filter.</td>
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
