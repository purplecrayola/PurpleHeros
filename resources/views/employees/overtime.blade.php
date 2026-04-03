@extends('layouts.master')
@section('content')
    {!! Toastr::message() !!}
    @include('employees.partials.self-service-style')
    <div class="page-wrapper self-service-modern">
        <div class="content container-fluid">
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">Overtime</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('em/dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Overtime</li>
                        </ul>
                        <p class="section-intro">Submit and track overtime requests with status visibility.</p>
                    </div>
                    <div class="col-auto float-right ml-auto">
                        <a href="#" class="btn add-btn" data-toggle="modal" data-target="#add_overtime"><i class="fa fa-plus"></i> Add Overtime</a>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3 col-sm-6">
                    <div class="stats-info">
                        <h6>Entries</h6>
                        <h4>{{ $summary['entries'] }} <span>requests</span></h4>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="stats-info">
                        <h6>Hours</h6>
                        <h4>{{ number_format($summary['hours'], 1) }} <span>total</span></h4>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="stats-info">
                        <h6>Pending</h6>
                        <h4>{{ $summary['pending'] }} <span>awaiting action</span></h4>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="stats-info">
                        <h6>Approved</h6>
                        <h4>{{ $summary['approved'] }} <span>approved</span></h4>
                    </div>
                </div>
            </div>

            <div class="panel-card">
                <div class="panel-head">
                    <h4 class="panel-title">Overtime Requests</h4>
                    <span class="panel-meta">{{ $overtimeEntries->count() }} requests</span>
                </div>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-striped custom-table mb-0 datatable">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>OT Date</th>
                                    <th class="text-center">OT Hours</th>
                                    <th>OT Type</th>
                                    <th>Description</th>
                                    <th class="text-center">Status</th>
                                    <th>Approved by</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($overtimeEntries as $entry)
                                    @php
                                        $avatar = \App\Support\MediaStorageManager::publicUrl($entry->avatar, 'assets/img/profiles/avatar-01.jpg', 'assets/images');
                                        $badgeClass = match ($entry->status) {
                                            'Approved' => 'success',
                                            'Rejected' => 'danger',
                                            default => 'warning',
                                        };
                                    @endphp
                                    <tr
                                        data-id="{{ $entry->id }}"
                                        data-user-id="{{ $entry->user_id }}"
                                        data-ot-date="{{ $entry->ot_date }}"
                                        data-hours="{{ $entry->hours }}"
                                        data-ot-type="{{ $entry->ot_type }}"
                                        data-status="{{ $entry->status }}"
                                        data-approved-by="{{ $entry->approved_by }}"
                                        data-description="{{ $entry->description }}"
                                    >
                                        <td>
                                            <h2 class="table-avatar blue-link">
                                                <a href="{{ url('employee/profile/' . $entry->user_id) }}" class="avatar"><img alt="{{ $entry->name }}" src="{{ $avatar }}"></a>
                                                <a href="{{ url('employee/profile/' . $entry->user_id) }}">{{ $entry->name }} <span>{{ $entry->position }}</span></a>
                                            </h2>
                                        </td>
                                        <td>{{ \Illuminate\Support\Carbon::parse($entry->ot_date)->format('d M Y') }}</td>
                                        <td class="text-center">{{ number_format((float) $entry->hours, 1) }}</td>
                                        <td>{{ $entry->ot_type }}</td>
                                        <td>{{ $entry->description ?: '—' }}</td>
                                        <td class="text-center"><span class="badge bg-inverse-{{ $badgeClass }}">{{ $entry->status }}</span></td>
                                        <td>{{ $entry->approved_by ?: 'Pending approval' }}</td>
                                        <td class="text-right">
                                            <div class="dropdown dropdown-action">
                                                <a href="#" class="action-icon dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><i class="material-icons">more_vert</i></a>
                                                <div class="dropdown-menu dropdown-menu-right">
                                                    <a class="dropdown-item editOvertime" href="#" data-toggle="modal" data-target="#edit_overtime"><i class="fa fa-pencil m-r-5"></i> Edit</a>
                                                    <a class="dropdown-item deleteOvertime" href="#" data-toggle="modal" data-target="#delete_overtime"><i class="fa fa-trash-o m-r-5"></i> Delete</a>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center table-empty">No overtime requests yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div id="add_overtime" class="modal custom-modal fade" role="dialog">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Overtime</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('form/overtime/save') }}" method="POST">
                            @csrf
                            <div class="form-group">
                                <label>Select Employee <span class="text-danger">*</span></label>
                                <select class="select @error('user_id') is-invalid @enderror" name="user_id">
                                    <option value="">-- Select --</option>
                                    @foreach ($users as $user)
                                        <option value="{{ $user->user_id }}">{{ $user->name }} ({{ $user->user_id }})</option>
                                    @endforeach
                                </select>
                                @error('user_id')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                            </div>
                            <div class="form-group">
                                <label>Overtime Date <span class="text-danger">*</span></label>
                                <input class="form-control @error('ot_date') is-invalid @enderror" type="date" name="ot_date" value="{{ old('ot_date') }}">
                                @error('ot_date')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                            </div>
                            <div class="form-group">
                                <label>Overtime Hours <span class="text-danger">*</span></label>
                                <input class="form-control @error('hours') is-invalid @enderror" type="number" step="0.5" name="hours" value="{{ old('hours') }}">
                                @error('hours')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                            </div>
                            <div class="form-group">
                                <label>Overtime Type <span class="text-danger">*</span></label>
                                <input class="form-control @error('ot_type') is-invalid @enderror" type="text" name="ot_type" value="{{ old('ot_type') }}" placeholder="Reason or category">
                                @error('ot_type')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                            </div>
                            <div class="form-group">
                                <label>Status <span class="text-danger">*</span></label>
                                <select class="select @error('status') is-invalid @enderror" name="status">
                                    <option value="Pending">Pending</option>
                                    <option value="Approved">Approved</option>
                                    <option value="Rejected">Rejected</option>
                                </select>
                                @error('status')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                            </div>
                            <div class="form-group">
                                <label>Approved By</label>
                                <input class="form-control @error('approved_by') is-invalid @enderror" type="text" name="approved_by" value="{{ old('approved_by') }}" placeholder="Optional approver name">
                                @error('approved_by')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                            </div>
                            <div class="form-group">
                                <label>Description</label>
                                <textarea rows="4" class="form-control @error('description') is-invalid @enderror" name="description">{{ old('description') }}</textarea>
                                @error('description')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                            </div>
                            <div class="submit-section">
                                <button class="btn btn-primary submit-btn" type="submit">Save Overtime</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div id="edit_overtime" class="modal custom-modal fade" role="dialog">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Overtime</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('form/overtime/update') }}" method="POST">
                            @csrf
                            <input type="hidden" name="id" id="e_overtime_id">
                            <div class="form-group">
                                <label>Select Employee <span class="text-danger">*</span></label>
                                <select class="select" name="user_id" id="e_overtime_user_id">
                                    @foreach ($users as $user)
                                        <option value="{{ $user->user_id }}">{{ $user->name }} ({{ $user->user_id }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Overtime Date <span class="text-danger">*</span></label>
                                <input class="form-control" type="date" name="ot_date" id="e_ot_date">
                            </div>
                            <div class="form-group">
                                <label>Overtime Hours <span class="text-danger">*</span></label>
                                <input class="form-control" type="number" step="0.5" name="hours" id="e_hours">
                            </div>
                            <div class="form-group">
                                <label>Overtime Type <span class="text-danger">*</span></label>
                                <input class="form-control" type="text" name="ot_type" id="e_ot_type">
                            </div>
                            <div class="form-group">
                                <label>Status <span class="text-danger">*</span></label>
                                <select class="select" name="status" id="e_status">
                                    <option value="Pending">Pending</option>
                                    <option value="Approved">Approved</option>
                                    <option value="Rejected">Rejected</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Approved By</label>
                                <input class="form-control" type="text" name="approved_by" id="e_approved_by">
                            </div>
                            <div class="form-group">
                                <label>Description</label>
                                <textarea rows="4" class="form-control" name="description" id="e_ot_description"></textarea>
                            </div>
                            <div class="submit-section">
                                <button class="btn btn-primary submit-btn" type="submit">Update Overtime</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal custom-modal fade" id="delete_overtime" role="dialog">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body">
                        <div class="form-header">
                            <h3>Delete Overtime</h3>
                            <p>Are you sure want to cancel this?</p>
                        </div>
                        <div class="modal-btn delete-action">
                            <form action="{{ route('form/overtime/delete') }}" method="POST">
                                @csrf
                                <input type="hidden" name="id" id="d_overtime_id">
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
            $(document).on('click', '.editOvertime', function() {
                var row = $(this).closest('tr');
                $('#e_overtime_id').val(row.data('id'));
                $('#e_overtime_user_id').val(row.data('user-id')).change();
                $('#e_ot_date').val(row.data('ot-date'));
                $('#e_hours').val(row.data('hours'));
                $('#e_ot_type').val(row.data('ot-type'));
                $('#e_status').val(row.data('status')).change();
                $('#e_approved_by').val(row.data('approved-by'));
                $('#e_ot_description').val(row.data('description'));
            });

            $(document).on('click', '.deleteOvertime', function() {
                var row = $(this).closest('tr');
                $('#d_overtime_id').val(row.data('id'));
            });
        </script>
    @endsection
@endsection
