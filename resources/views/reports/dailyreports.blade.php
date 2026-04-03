@extends('layouts.master')
@section('content')
    {!! Toastr::message() !!}
    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">Daily Attendance Report</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Daily Attendance Report</li>
                        </ul>
                    </div>
                </div>
            </div>

            <form method="GET" action="{{ route('form/daily/reports/page') }}" class="row filter-row mb-4">
                <div class="col-sm-6 col-md-4">
                    <div class="form-group form-focus">
                        <input class="form-control floating" type="date" name="report_date" value="{{ $selectedDate }}">
                        <label class="focus-label">Report Date</label>
                    </div>
                </div>
                <div class="col-sm-6 col-md-2">
                    <button type="submit" class="btn btn-success btn-block">Load</button>
                </div>
                <div class="col-sm-6 col-md-2">
                    <a href="{{ route('form/daily/reports/page') }}" class="btn btn-outline-secondary btn-block">Reset</a>
                </div>
            </form>

            <div class="row justify-content-center">
                <div class="col-md-3 col-sm-6 d-flex">
                    <div class="card flex-fill">
                        <div class="card-body text-center">
                            <h3><b>{{ $summary['total_employees'] }}</b></h3>
                            <p>Total Employees</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 d-flex">
                    <div class="card flex-fill">
                        <div class="card-body text-center">
                            <h3 class="text-success"><b>{{ $summary['present'] }}</b></h3>
                            <p>Present</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 d-flex">
                    <div class="card flex-fill">
                        <div class="card-body text-center">
                            <h3 class="text-info"><b>{{ $summary['remote'] }}</b></h3>
                            <p>Remote</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 d-flex">
                    <div class="card flex-fill">
                        <div class="card-body text-center">
                            <h3 class="text-danger"><b>{{ $summary['absent'] }}</b></h3>
                            <p>Absent</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-striped custom-table mb-0 datatable">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Date</th>
                            <th>Department</th>
                            <th>Status</th>
                            <th>Check In</th>
                            <th>Check Out</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($attendanceRows as $row)
                            @php
                                $avatar = \App\Support\MediaStorageManager::publicUrl($row->avatar ?? null, 'assets/img/profiles/photo_defaults.jpg', 'assets/images');
                                $departmentName = $row->profile_department ?: $row->user_department ?: 'Unassigned';
                            @endphp
                            <tr>
                                <td>
                                    <h2 class="table-avatar">
                                        <a href="{{ url('employee/profile/' . $row->user_id) }}" class="avatar"><img alt="{{ $row->name }}" src="{{ $avatar }}"></a>
                                        <a href="{{ url('employee/profile/' . $row->user_id) }}">{{ $row->name }} <span>{{ $row->user_id }}</span></a>
                                    </h2>
                                </td>
                                <td>{{ \Carbon\Carbon::parse($row->attendance_date)->format('d M Y') }}</td>
                                <td>{{ $departmentName }}</td>
                                <td><span class="badge bg-inverse-info">{{ ucfirst($row->status) }}</span></td>
                                <td>{{ $row->check_in ?: 'N/A' }}</td>
                                <td>{{ $row->check_out ?: 'N/A' }}</td>
                                <td>{{ $row->notes ?: 'N/A' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">No attendance records exist for {{ \Carbon\Carbon::parse($selectedDate)->format('d M Y') }}.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
