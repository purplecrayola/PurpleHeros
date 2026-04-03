@extends('layouts.master')
@section('content')
    @include('employees.partials.self-service-style')
    <div class="page-wrapper self-service-modern">
        <div class="content container-fluid">
            @php
                $hasRecords = $records->isNotEmpty();
            @endphp
            <style>
                .attendance-empty-note {
                    border: 1px dashed rgba(15, 23, 42, 0.15);
                    border-radius: 12px;
                    background: rgba(255, 255, 255, 0.65);
                    padding: 12px 14px;
                    color: rgba(15, 23, 42, 0.72);
                    font-size: 14px;
                    margin-top: 12px;
                }
                .attendance-actions {
                    display: flex;
                    gap: 10px;
                    margin-top: 14px;
                }
                .attendance-actions form {
                    flex: 1;
                }
                .attendance-actions .btn {
                    width: 100%;
                }
            </style>
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">My Attendance</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('em/dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Attendance</li>
                        </ul>
                        <p class="section-intro">Review attendance history, working hours, and recent activity.</p>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="card punch-status">
                        <div class="card-body">
                            <h5 class="card-title">Latest Record <small class="text-muted">{{ $latestRecord ? \Illuminate\Support\Carbon::parse($latestRecord->attendance_date)->format('d M Y') : 'No data' }}</small></h5>
                            <div class="punch-det">
                                <h6>Status</h6>
                                <p>{{ $latestRecord?->status ?? 'No attendance logged yet' }}</p>
                            </div>
                            <div class="punch-info">
                                <div class="punch-hours">
                                    <span>{{ $latestRecord ? number_format($latestRecord->work_minutes / 60, 1) . ' hrs' : '0 hrs' }}</span>
                                </div>
                            </div>
                            <div class="statistics">
                                <div class="row">
                                    <div class="col-md-6 col-6 text-center">
                                        <div class="stats-box">
                                            <p>Break</p>
                                            <h6>{{ $latestRecord ? number_format($latestRecord->break_minutes / 60, 1) . ' hrs' : '0 hrs' }}</h6>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-6 text-center">
                                        <div class="stats-box">
                                            <p>Overtime</p>
                                            <h6>{{ $latestRecord ? number_format($latestRecord->overtime_minutes / 60, 1) . ' hrs' : '0 hrs' }}</h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="attendance-actions">
                                <form action="{{ route('attendance/employee/check-in') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-success" {{ $canCheckIn ? '' : 'disabled' }}>Check In</button>
                                </form>
                                <form action="{{ route('attendance/employee/check-out') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-primary" {{ $canCheckOut ? '' : 'disabled' }}>Check Out</button>
                                </form>
                            </div>
                            @if($todayRecord?->check_in || $todayRecord?->check_out)
                                <div class="attendance-empty-note mb-0 mt-3">
                                    Today:
                                    {{ $todayRecord?->check_in ? 'In ' . \Illuminate\Support\Carbon::parse($todayRecord->check_in)->format('h:i A') : 'Not checked in' }}
                                    ·
                                    {{ $todayRecord?->check_out ? 'Out ' . \Illuminate\Support\Carbon::parse($todayRecord->check_out)->format('h:i A') : 'Not checked out' }}
                                </div>
                            @endif
                            @if($hasRecords)
                                <div class="attendance-empty-note mb-0 mt-3">Attendance entries are shown from recorded daily data for this period.</div>
                            @else
                                <div class="attendance-empty-note mb-0 mt-3">No attendance has been recorded for this period yet. Try another month/year filter or contact HR to confirm entries.</div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card att-statistics">
                        <div class="card-body">
                            <h5 class="card-title">This Period</h5>
                            @if(! $hasRecords)
                                <div class="attendance-empty-note">No period summary yet because there are no attendance records in the selected month.</div>
                            @endif
                            <div class="stats-list">
                                <div class="stats-info">
                                    <p>Present <strong>{{ $summary['present'] }} days</strong></p>
                                </div>
                                <div class="stats-info">
                                    <p>Remote <strong>{{ $summary['remote'] }} days</strong></p>
                                </div>
                                <div class="stats-info">
                                    <p>Late <strong>{{ $summary['late'] }} days</strong></p>
                                </div>
                                <div class="stats-info">
                                    <p>Absent <strong>{{ $summary['absent'] }} days</strong></p>
                                </div>
                                <div class="stats-info">
                                    <p>Total Hours <strong>{{ $summary['work_hours'] }}</strong></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card recent-activity">
                        <div class="card-body">
                            <h5 class="card-title">Recent Activity</h5>
                            @if($activity->isNotEmpty())
                                <ul class="res-activity-list">
                                    @foreach ($activity as $item)
                                        <li>
                                            <p class="mb-0">{{ $item->status }} on {{ \Illuminate\Support\Carbon::parse($item->attendance_date)->format('d M Y') }}</p>
                                            <p class="res-activity-time">
                                                <i class="fa fa-clock-o"></i>
                                                {{ $item->check_in ? \Illuminate\Support\Carbon::parse($item->check_in)->format('h:i A') : 'No check in' }}
                                                to
                                                {{ $item->check_out ? \Illuminate\Support\Carbon::parse($item->check_out)->format('h:i A') : 'No check out' }}
                                            </p>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <div class="attendance-empty-note">No activity logged yet for the selected period.</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="panel-card">
                <div class="panel-head">
                    <h4 class="panel-title">Period Filter</h4>
                    <span class="panel-meta">{{ $records->count() }} records</span>
                </div>
                <div class="panel-body">
                    <div class="row filter-row mb-0">
                        <div class="col-sm-4">
                            <form action="{{ route('attendance/employee/page') }}" method="GET" class="mb-0">
                                <div class="form-group form-focus select-focus">
                                    <select class="select floating" name="month">
                                        @foreach ($months as $monthValue => $monthLabel)
                                            <option value="{{ $monthValue }}" {{ (int) $filters['month'] === (int) $monthValue ? 'selected' : '' }}>{{ $monthLabel }}</option>
                                        @endforeach
                                    </select>
                                    <label class="focus-label">Month</label>
                                </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="form-group form-focus select-focus">
                                <select class="select floating" name="year">
                                    @foreach ($years as $year)
                                        <option value="{{ $year }}" {{ (int) $filters['year'] === (int) $year ? 'selected' : '' }}>{{ $year }}</option>
                                    @endforeach
                                </select>
                                <label class="focus-label">Year</label>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <button type="submit" class="btn btn-success btn-block">Apply Filter</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="panel-card">
                <div class="panel-head">
                    <h4 class="panel-title">Attendance History</h4>
                    <span class="panel-meta">{{ \Illuminate\Support\Carbon::createFromDate((int) $filters['year'], (int) $filters['month'], 1)->format('F Y') }}</span>
                </div>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-striped custom-table datatable">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Punch In</th>
                                    <th>Punch Out</th>
                                    <th>Work Hrs</th>
                                    <th>Break</th>
                                    <th>Overtime</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if($records->isNotEmpty())
                                    @foreach ($records as $record)
                                    @php
                                        $badgeClass = match ($record->status) {
                                            'Present' => 'success',
                                            'Remote' => 'info',
                                            'Late' => 'warning',
                                            'Absent' => 'danger',
                                            default => 'secondary',
                                        };
                                    @endphp
                                    <tr>
                                        <td>{{ \Illuminate\Support\Carbon::parse($record->attendance_date)->format('d M Y') }}</td>
                                        <td><span class="badge bg-inverse-{{ $badgeClass }}">{{ $record->status }}</span></td>
                                        <td>{{ $record->check_in ? \Illuminate\Support\Carbon::parse($record->check_in)->format('h:i A') : '-' }}</td>
                                        <td>{{ $record->check_out ? \Illuminate\Support\Carbon::parse($record->check_out)->format('h:i A') : '-' }}</td>
                                        <td>{{ number_format($record->work_minutes / 60, 1) }}</td>
                                        <td>{{ number_format($record->break_minutes / 60, 1) }}</td>
                                        <td>{{ number_format($record->overtime_minutes / 60, 1) }}</td>
                                        <td>{{ $record->notes ?: '-' }}</td>
                                    </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="8" class="text-center table-empty">No attendance records found for this period.</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
