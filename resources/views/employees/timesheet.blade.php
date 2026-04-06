@extends('layouts.master')
@section('content')
    {!! Toastr::message() !!}
        <div class="page-wrapper">
        <div class="content container-fluid">
            @include('employees.partials.employee-topbar', ['context' => 'Timesheet workspace'])
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">Timesheet</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('em/dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Timesheet</li>
                        </ul>
                        <p class="section-intro">Log work done by day and maintain project-level visibility for your manager.</p>
                    </div>
                    <div class="col-auto float-right ml-auto">
                        <a href="#" class="btn add-btn" data-toggle="modal" data-target="#add_todaywork"><i class="fa fa-plus"></i> Log Time Entry</a>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3 col-sm-6">
                    <div class="stats-info">
                        <h6>Entries</h6>
                        <h4>{{ $summary['entries'] }} <span>logged items</span></h4>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="stats-info">
                        <h6>Assigned Hours</h6>
                        <h4>{{ $summary['assigned_hours'] }} <span>hours</span></h4>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="stats-info">
                        <h6>Worked Hours</h6>
                        <h4>{{ $summary['worked_hours'] }} <span>hours</span></h4>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="stats-info">
                        <h6>Active Projects</h6>
                        <h4>{{ $summary['active_projects'] }} <span>this sample set</span></h4>
                    </div>
                </div>
            </div>

            <div class="panel-card">
                <div class="panel-head">
                    <h4 class="panel-title">Timesheet Entries</h4>
                    <span class="panel-meta">{{ $timesheets->count() }} entries</span>
                </div>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-striped custom-table mb-0 datatable">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Date</th>
                                    <th>Project</th>
                                    <th class="text-center">Assigned Hours</th>
                                    <th class="text-center">Worked Hours</th>
                                    <th class="d-none d-sm-table-cell">Description</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($timesheets as $entry)
                                    @php
                                        $avatar = \App\Support\MediaStorageManager::publicUrl($entry->avatar, 'assets/img/profiles/avatar-01.jpg');
                                    @endphp
                                    <tr
                                        data-id="{{ $entry->id }}"
                                        data-user-id="{{ $entry->user_id }}"
                                        data-work-date="{{ $entry->work_date }}"
                                        data-project-name="{{ $entry->project_name }}"
                                        data-assigned-hours="{{ $entry->assigned_hours }}"
                                        data-worked-hours="{{ $entry->worked_hours }}"
                                        data-description="{{ $entry->description }}"
                                    >
                                        <td>
                                            <h2 class="table-avatar">
                                                <a href="{{ url('employee/profile/' . $entry->user_id) }}" class="avatar"><img alt="{{ $entry->name }}" src="{{ $avatar }}"></a>
                                                <a href="{{ url('employee/profile/' . $entry->user_id) }}">{{ $entry->name }} <span>{{ $entry->position }}</span></a>
                                            </h2>
                                        </td>
                                        <td>{{ \Illuminate\Support\Carbon::parse($entry->work_date)->format('d M Y') }}</td>
                                        <td>{{ $entry->project_name }}</td>
                                        <td class="text-center">{{ $entry->assigned_hours }}</td>
                                        <td class="text-center">{{ $entry->worked_hours }}</td>
                                        <td class="d-none d-sm-table-cell col-md-4">{{ $entry->description ?: '—' }}</td>
                                        <td class="text-right">
                                            <div class="dropdown dropdown-action">
                                                <a href="#" class="action-icon dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><i class="material-icons">more_vert</i></a>
                                                <div class="dropdown-menu dropdown-menu-right">
                                                    <a class="dropdown-item editTimesheet" href="#" data-toggle="modal" data-target="#edit_todaywork"><i class="fa fa-pencil m-r-5"></i> Edit</a>
                                                    <a class="dropdown-item deleteTimesheet" href="#" data-toggle="modal" data-target="#delete_workdetail"><i class="fa fa-trash-o m-r-5"></i> Delete</a>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center table-empty">No timesheet entries yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div id="add_todaywork" class="modal custom-modal fade" role="dialog">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Log Time Entry</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('employee/timesheets/save') }}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>Employee</label>
                                        <select class="select @error('user_id') is-invalid @enderror" name="user_id">
                                            <option value="">-- Select --</option>
                                            @foreach ($users as $user)
                                                <option value="{{ $user->user_id }}">{{ $user->name }} ({{ $user->user_id }})</option>
                                            @endforeach
                                        </select>
                                        @error('user_id')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>Date</label>
                                        <input class="form-control @error('work_date') is-invalid @enderror" type="date" name="work_date" value="{{ old('work_date') }}">
                                        @error('work_date')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>Project</label>
                                        <input class="form-control @error('project_name') is-invalid @enderror" type="text" name="project_name" value="{{ old('project_name') }}" placeholder="Project or workstream">
                                        @error('project_name')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label>Assigned Hours</label>
                                        <input class="form-control @error('assigned_hours') is-invalid @enderror" type="number" name="assigned_hours" value="{{ old('assigned_hours') }}" min="0" max="24">
                                        @error('assigned_hours')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label>Worked Hours</label>
                                        <input class="form-control @error('worked_hours') is-invalid @enderror" type="number" name="worked_hours" value="{{ old('worked_hours') }}" min="0" max="24">
                                        @error('worked_hours')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Description</label>
                                <textarea rows="4" class="form-control @error('description') is-invalid @enderror" name="description" placeholder="Summary of work completed">{{ old('description') }}</textarea>
                                @error('description')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                            </div>
                            <div class="submit-section">
                                <button class="btn btn-primary submit-btn" type="submit">Save Time Entry</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div id="edit_todaywork" class="modal custom-modal fade" role="dialog">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Time Entry</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('employee/timesheets/update') }}" method="POST">
                            @csrf
                            <input type="hidden" name="id" id="e_timesheet_id">
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>Employee</label>
                                        <select class="select" name="user_id" id="e_timesheet_user_id">
                                            @foreach ($users as $user)
                                                <option value="{{ $user->user_id }}">{{ $user->name }} ({{ $user->user_id }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>Date</label>
                                        <input class="form-control" type="date" name="work_date" id="e_work_date">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label>Project</label>
                                        <input class="form-control" type="text" name="project_name" id="e_project_name">
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label>Assigned Hours</label>
                                        <input class="form-control" type="number" name="assigned_hours" id="e_assigned_hours" min="0" max="24">
                                    </div>
                                </div>
                                <div class="col-sm-3">
                                    <div class="form-group">
                                        <label>Worked Hours</label>
                                        <input class="form-control" type="number" name="worked_hours" id="e_worked_hours" min="0" max="24">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Description</label>
                                <textarea rows="4" class="form-control" name="description" id="e_description"></textarea>
                            </div>
                            <div class="submit-section">
                                <button class="btn btn-primary submit-btn" type="submit">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal custom-modal fade" id="delete_workdetail" role="dialog">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body">
                        <div class="form-header">
                            <h3>Delete Time Entry</h3>
                            <p>Are you sure you want to delete this entry?</p>
                        </div>
                        <div class="modal-btn delete-action">
                            <form action="{{ route('employee/timesheets/delete') }}" method="POST">
                                @csrf
                                <input type="hidden" name="id" id="d_timesheet_id">
                                <div class="row">
                                    <div class="col-6">
                                        <button type="submit" class="btn btn-primary continue-btn submit-btn">Delete</button>
                                    </div>
                                    <div class="col-6">
                                        <a href="javascript:void(0);" data-dismiss="modal" class="btn btn-primary cancel-btn">Cancel</a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @section('script')
        <script>
            $(document).on('click', '.editTimesheet', function() {
                var row = $(this).closest('tr');
                $('#e_timesheet_id').val(row.data('id'));
                $('#e_timesheet_user_id').val(row.data('user-id')).change();
                $('#e_work_date').val(row.data('work-date'));
                $('#e_project_name').val(row.data('project-name'));
                $('#e_assigned_hours').val(row.data('assigned-hours'));
                $('#e_worked_hours').val(row.data('worked-hours'));
                $('#e_description').val(row.data('description'));
            });

            $(document).on('click', '.deleteTimesheet', function() {
                var row = $(this).closest('tr');
                $('#d_timesheet_id').val(row.data('id'));
            });
        </script>
    @endsection
@endsection
