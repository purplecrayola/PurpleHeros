@extends('layouts.master')
@section('content')
    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">Leaves <span id="year"></span></h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Leaves</li>
                        </ul>
                    </div>
                    <div class="col-auto float-right ml-auto">
                        <a href="#" class="btn add-btn" data-toggle="modal" data-target="#add_leave"><i class="fa fa-plus"></i> Add Leave</a>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3">
                    <div class="stats-info">
                        <h6>Total Leave Days</h6>
                        <h4>{{ $stats['total'] }}</h4>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-info">
                        <h6>Annual Leave</h6>
                        <h4>{{ $stats['annual'] }} <span>Total</span></h4>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-info">
                        <h6>Sick Leave</h6>
                        <h4>{{ $stats['sick'] }} <span>Total</span></h4>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stats-info">
                        <h6>Maternity Leave</h6>
                        <h4>{{ $stats['maternity'] }}</h4>
                    </div>
                </div>
            </div>

            <div class="row filter-row">
                <div class="col-sm-6 col-md-3 col-lg-3 col-xl-2 col-12">
                    <div class="form-group form-focus">
                        <input type="text" class="form-control floating" disabled>
                        <label class="focus-label">Employee Name</label>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3 col-lg-3 col-xl-2 col-12">
                    <div class="form-group form-focus select-focus">
                        <select class="select floating" disabled>
                            <option> -- Select -- </option>
                            <option>Casual Leave</option>
                            <option>Medical Leave</option>
                            <option>Loss of Pay</option>
                        </select>
                        <label class="focus-label">Leave Type</label>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3 col-lg-3 col-xl-2 col-12">
                    <div class="form-group form-focus select-focus">
                        <select class="select floating" disabled>
                            <option> -- Select -- </option>
                            <option> Pending </option>
                            <option> Approved </option>
                            <option> Rejected </option>
                        </select>
                        <label class="focus-label">Leave Status</label>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3 col-lg-3 col-xl-2 col-12">
                    <div class="form-group form-focus">
                        <div class="cal-icon">
                            <input class="form-control floating datetimepicker" type="text" disabled>
                        </div>
                        <label class="focus-label">From</label>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3 col-lg-3 col-xl-2 col-12">
                    <div class="form-group form-focus">
                        <div class="cal-icon">
                            <input class="form-control floating datetimepicker" type="text" disabled>
                        </div>
                        <label class="focus-label">To</label>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3 col-lg-3 col-xl-2 col-12">
                    <button class="btn btn-success btn-block" type="button" disabled>Search</button>
                </div>
            </div>

            {!! Toastr::message() !!}

            <div class="row">
                <div class="col-md-12">
                    <div class="table-responsive">
                        <table class="table table-striped custom-table mb-0 datatable">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Leave Type</th>
                                    <th>From</th>
                                    <th>To</th>
                                    <th>No of Days</th>
                                    <th>Reason</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($leaves as $items)
                                    <tr data-id="{{ $items->id }}" data-from-date="{{ $items->from_date }}" data-to-date="{{ $items->to_date }}" data-leave-type="{{ $items->leave_type }}" data-leave-reason="{{ $items->leave_reason }}" data-day-label="{{ $items->day }} Day{{ (int) $items->day > 1 ? 's' : '' }}">
                                        <td>
                                            <h2 class="table-avatar">
                                                <a href="{{ url('employee/profile/'.$items->user_id) }}" class="avatar"><img alt="" src="{{ \App\Support\MediaStorageManager::publicUrl($items->avatar, 'assets/img/user.jpg', 'assets/images') }}" alt="{{ $items->name }}"></a>
                                                <a href="{{ url('employee/profile/'.$items->user_id) }}">{{ $items->name }}<span>{{ $items->position }}</span></a>
                                            </h2>
                                        </td>
                                        <td class="leave_type">{{ $items->leave_type }}</td>
                                        <td>{{ date('d F, Y', strtotime($items->from_date)) }}</td>
                                        <td>{{ date('d F, Y', strtotime($items->to_date)) }}</td>
                                        <td class="day">{{ $items->day }} Day{{ (int) $items->day > 1 ? 's' : '' }}</td>
                                        <td class="leave_reason">{{ $items->leave_reason }}</td>
                                        <td class="text-center">
                                            <div class="action-label">
                                                <a class="btn btn-white btn-sm btn-rounded" href="javascript:void(0);">
                                                    <i class="fa fa-dot-circle-o text-purple"></i> New
                                                </a>
                                            </div>
                                        </td>
                                        <td class="text-right">
                                            <div class="dropdown dropdown-action">
                                                <a href="#" class="action-icon dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><i class="material-icons">more_vert</i></a>
                                                <div class="dropdown-menu dropdown-menu-right">
                                                    <a class="dropdown-item leaveUpdate" href="#" data-toggle="modal" data-target="#edit_leave"><i class="fa fa-pencil m-r-5"></i> Edit</a>
                                                    <a class="dropdown-item leaveDelete" href="#" data-toggle="modal" data-target="#delete_approve"><i class="fa fa-trash-o m-r-5"></i> Delete</a>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted">No leave requests yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div id="add_leave" class="modal custom-modal fade" role="dialog">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Leave</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('form/leaves/save') }}" method="POST">
                            @csrf
                            <div class="form-group">
                                <label>Leave Type <span class="text-danger">*</span></label>
                                <select class="select" id="leaveType" name="leave_type">
                                    <option selected disabled>Select Leave Type</option>
                                    @foreach ($leaveTypeOptions as $typeValue => $typeLabel)
                                        <option value="{{ $typeValue }}">{{ $typeLabel }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <input type="hidden" class="form-control" id="user_id" name="user_id" value="{{ Auth::user()->user_id }}">
                            <div class="form-group">
                                <label>From <span class="text-danger">*</span></label>
                                <div class="cal-icon">
                                    <input type="text" class="form-control datetimepicker" id="from_date" name="from_date">
                                </div>
                            </div>
                            <div class="form-group">
                                <label>To <span class="text-danger">*</span></label>
                                <div class="cal-icon">
                                    <input type="text" class="form-control datetimepicker" id="to_date" name="to_date">
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Leave Reason <span class="text-danger">*</span></label>
                                <textarea rows="4" class="form-control" id="leave_reason" name="leave_reason"></textarea>
                            </div>
                            <div class="submit-section">
                                <button type="submit" class="btn btn-primary submit-btn">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div id="edit_leave" class="modal custom-modal fade" role="dialog">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Leave</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('form/leaves/edit') }}" method="POST">
                            @csrf
                            <input type="hidden" id="e_id" name="id" value="">
                            <div class="form-group">
                                <label>Leave Type <span class="text-danger">*</span></label>
                                <select class="select" id="e_leave_type" name="leave_type">
                                    <option selected disabled>Select Leave Type</option>
                                    @foreach ($leaveTypeOptions as $typeValue => $typeLabel)
                                        <option value="{{ $typeValue }}">{{ $typeLabel }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label>From <span class="text-danger">*</span></label>
                                <div class="cal-icon">
                                    <input type="text" class="form-control datetimepicker" id="e_from_date" name="from_date" value="">
                                </div>
                            </div>
                            <div class="form-group">
                                <label>To <span class="text-danger">*</span></label>
                                <div class="cal-icon">
                                    <input type="text" class="form-control datetimepicker" id="e_to_date" name="to_date" value="">
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Number of days <span class="text-danger">*</span></label>
                                <input class="form-control" readonly type="text" id="e_number_of_days" name="number_of_days" value="">
                            </div>
                            <div class="form-group">
                                <label>Leave Reason <span class="text-danger">*</span></label>
                                <textarea rows="4" class="form-control" id="e_leave_reason" name="leave_reason"></textarea>
                            </div>
                            <div class="submit-section">
                                <button type="submit" class="btn btn-primary submit-btn">Save</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal custom-modal fade" id="approve_leave" role="dialog">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body">
                        <div class="form-header">
                            <h3>Leave Approve</h3>
                            <p>This status workflow is not wired yet in the interim build.</p>
                        </div>
                        <div class="modal-btn delete-action">
                            <div class="row">
                                <div class="col-6">
                                    <a href="javascript:void(0);" data-dismiss="modal" class="btn btn-primary continue-btn">Close</a>
                                </div>
                                <div class="col-6">
                                    <a href="javascript:void(0);" data-dismiss="modal" class="btn btn-primary cancel-btn">Cancel</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal custom-modal fade" id="delete_approve" role="dialog">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body">
                        <div class="form-header">
                            <h3>Delete Leave</h3>
                            <p>Are you sure want to delete this leave?</p>
                        </div>
                        <div class="modal-btn delete-action">
                            <form action="{{ route('form/leaves/edit/delete') }}" method="POST">
                                @csrf
                                <input type="hidden" name="id" class="e_id" value="">
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
        document.getElementById('year').innerHTML = new Date().getFullYear();
    </script>
    <script>
        $(document).on('click', '.leaveUpdate', function() {
            var row = $(this).closest('tr');
            $('#e_id').val(row.data('id'));
            $('#e_number_of_days').val(row.data('day-label'));
            $('#e_from_date').val(row.data('from-date'));
            $('#e_to_date').val(row.data('to-date'));
            $('#e_leave_reason').val(row.data('leave-reason'));
            $('#e_leave_type').val(row.data('leave-type')).change();
        });
    </script>
    <script>
        $(document).on('click', '.leaveDelete', function() {
            var row = $(this).closest('tr');
            $('.e_id').val(row.data('id'));
        });
    </script>
    @endsection
@endsection
