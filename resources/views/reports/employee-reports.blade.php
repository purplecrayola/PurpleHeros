@extends('layouts.master')
@section('content')
    {!! Toastr::message() !!}
    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">Employee Report</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Employee Report</li>
                        </ul>
                    </div>
                </div>
            </div>

            <form method="GET" action="{{ route('form/employee/reports/page') }}" class="row filter-row mb-4">
                <div class="col-sm-6 col-md-4">
                    <div class="form-group form-focus">
                        <input class="form-control floating" name="employee" type="text" value="{{ $employee }}">
                        <label class="focus-label">Employee Name, ID, or Email</label>
                    </div>
                </div>
                <div class="col-sm-6 col-md-4">
                    <div class="form-group form-focus select-focus">
                        <select class="select floating" name="department">
                            <option value="">All Departments</option>
                            @foreach ($departments as $departmentOption)
                                <option value="{{ $departmentOption }}" {{ $department === $departmentOption ? 'selected' : '' }}>{{ $departmentOption }}</option>
                            @endforeach
                        </select>
                        <label class="focus-label">Department</label>
                    </div>
                </div>
                <div class="col-sm-6 col-md-2">
                    <button type="submit" class="btn btn-success btn-block">Search</button>
                </div>
                <div class="col-sm-6 col-md-2">
                    <a href="{{ route('form/employee/reports/page') }}" class="btn btn-outline-secondary btn-block">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-striped custom-table mb-0 datatable">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Department</th>
                            <th>Designation</th>
                            <th>Join Date</th>
                            <th>Status</th>
                            <th>Phone</th>
                            <th>Salary</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($employees as $employeeRow)
                            @php
                                $avatar = \App\Support\MediaStorageManager::publicUrl($employeeRow->avatar ?? null, 'assets/img/profiles/photo_defaults.jpg', 'assets/images');
                                $departmentName = $employeeRow->profile_department ?: $employeeRow->user_department ?: 'Unassigned';
                                $designation = $employeeRow->designation ?: $employeeRow->position ?: 'Not set';
                            @endphp
                            <tr>
                                <td>
                                    <h2 class="table-avatar">
                                        <a href="{{ url('employee/profile/' . $employeeRow->user_id) }}" class="avatar"><img alt="{{ $employeeRow->name }}" src="{{ $avatar }}"></a>
                                        <a href="{{ url('employee/profile/' . $employeeRow->user_id) }}">{{ $employeeRow->name }} <span>{{ $employeeRow->user_id }}</span></a>
                                    </h2>
                                    <div class="small text-muted">{{ $employeeRow->email }}</div>
                                </td>
                                <td>{{ $departmentName }}</td>
                                <td>{{ $designation }}</td>
                                <td>{{ \Carbon\Carbon::parse($employeeRow->join_date)->format('d M Y') }}</td>
                                <td><span class="badge bg-inverse-success">{{ $employeeRow->status ?: 'Active' }}</span></td>
                                <td>{{ $employeeRow->phone_number ?: 'N/A' }}</td>
                                <td>{{ $employeeRow->salary ? '$' . number_format((float) $employeeRow->salary, 2) : 'N/A' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">No employees matched the selected filters.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
