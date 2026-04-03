@extends('layouts.master')
@section('content')
    {!! Toastr::message() !!}
    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">Leave Report</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Leave Report</li>
                        </ul>
                    </div>
                </div>
            </div>

            <form method="GET" action="{{ route('form/leave/reports/page') }}" class="row filter-row mb-4">
                <div class="col-sm-6 col-md-4">
                    <div class="form-group form-focus">
                        <input class="form-control floating" type="text" name="employee" value="{{ $employee }}">
                        <label class="focus-label">Employee Name or ID</label>
                    </div>
                </div>
                <div class="col-sm-6 col-md-2">
                    <button type="submit" class="btn btn-success btn-block">Search</button>
                </div>
                <div class="col-sm-6 col-md-2">
                    <a href="{{ route('form/leave/reports/page') }}" class="btn btn-outline-secondary btn-block">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-striped custom-table mb-0 datatable">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Department</th>
                            <th>Leave Type</th>
                            <th>Date Range</th>
                            <th>Days</th>
                            <th>Reason</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($leaves as $leave)
                            @php
                                $avatar = \App\Support\MediaStorageManager::publicUrl($leave->avatar ?? null, 'assets/img/profiles/photo_defaults.jpg', 'assets/images');
                                $departmentName = $leave->profile_department ?: $leave->user_department ?: 'Unassigned';
                            @endphp
                            <tr>
                                <td>
                                    <h2 class="table-avatar">
                                        <a href="{{ url('employee/profile/' . $leave->user_id) }}" class="avatar"><img alt="{{ $leave->name }}" src="{{ $avatar }}"></a>
                                        <a href="{{ url('employee/profile/' . $leave->user_id) }}">{{ $leave->name }} <span>{{ $leave->user_id }}</span></a>
                                    </h2>
                                </td>
                                <td>{{ $departmentName }}</td>
                                <td>{{ $leave->leave_type }}</td>
                                <td>{{ \Carbon\Carbon::parse($leave->from_date)->format('d M Y') }} to {{ \Carbon\Carbon::parse($leave->to_date)->format('d M Y') }}</td>
                                <td>{{ $leave->day }}</td>
                                <td>{{ $leave->leave_reason ?: 'N/A' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">No leave records matched the selected filters.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
