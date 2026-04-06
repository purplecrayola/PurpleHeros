@extends('layouts.master')
@section('content')
        <div class="page-wrapper">
        <div class="content container-fluid">
            @include('employees.partials.employee-topbar', ['context' => 'Holiday calendar workspace'])
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">Holidays</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('em/dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Holidays</li>
                        </ul>
                        <p class="section-intro">View the company holiday calendar and upcoming dates.</p>
                    </div>
                    @if(Auth::user()?->isAdmin())
                        <div class="col-auto float-right ml-auto">
                            <a href="#" class="btn add-btn" data-toggle="modal" data-target="#add_holiday"><i class="fa fa-plus"></i> Add Holiday</a>
                        </div>
                    @endif
                </div>
            </div>

            {!! Toastr::message() !!}

            @php
                $today = \Carbon\Carbon::today();
            @endphp

            <div class="row mb-4">
                <div class="col-md-4 d-flex">
                    <div class="card flex-fill">
                        <div class="card-body text-center">
                            <h3 class="mb-1">{{ $holidays->count() }}</h3>
                            <p class="mb-0 text-muted">Configured Holidays</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 d-flex">
                    <div class="card flex-fill">
                        <div class="card-body text-center">
                            <h3 class="mb-1">{{ $holidays->filter(fn ($holiday) => \Carbon\Carbon::parse($holiday->date_holiday)->greaterThanOrEqualTo($today))->count() }}</h3>
                            <p class="mb-0 text-muted">Upcoming Holidays</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 d-flex">
                    <div class="card flex-fill">
                        <div class="card-body text-center">
                            <h3 class="mb-1">{{ optional($holidays->sortBy('date_holiday')->first())->date_holiday ? \Carbon\Carbon::parse($holidays->sortBy('date_holiday')->first()->date_holiday)->format('d M Y') : 'N/A' }}</h3>
                            <p class="mb-0 text-muted">Next Holiday</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="panel-card">
                <div class="panel-head">
                    <h4 class="panel-title">Holiday Calendar</h4>
                    <span class="panel-meta">{{ $holidays->count() }} holidays</span>
                </div>
                <div class="panel-body">
                    <div class="table-responsive">
                        <table class="table table-striped custom-table datatable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Title</th>
                                    <th>Date</th>
                                    <th>Day</th>
                                    <th>Status</th>
                                    @if(Auth::user()?->isAdmin())
                                        <th class="text-right">Action</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($holidays as $index => $holiday)
                                    @php
                                        $holidayDate = \Carbon\Carbon::parse($holiday->date_holiday);
                                        $isUpcoming = $holidayDate->greaterThanOrEqualTo($today);
                                    @endphp
                                    <tr class="{{ $isUpcoming ? 'holiday-upcoming' : 'holiday-completed' }}">
                                        <td>{{ $index + 1 }}</td>
                                        <td class="holidayName">{{ $holiday->name_holiday }}</td>
                                        <td class="holidayDateRaw d-none">{{ $holiday->date_holiday }}</td>
                                        <td>{{ $holidayDate->format('d F, Y') }}</td>
                                        <td>{{ $holidayDate->format('l') }}</td>
                                        <td>
                                            <span class="badge {{ $isUpcoming ? 'bg-inverse-success' : 'bg-inverse-secondary' }}">
                                                {{ $isUpcoming ? 'Upcoming' : 'Completed' }}
                                            </span>
                                        </td>
                                        @if(Auth::user()?->isAdmin())
                                            <td class="text-right">
                                                <div class="dropdown dropdown-action">
                                                    <a href="#" class="action-icon dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><i class="material-icons">more_vert</i></a>
                                                    <div class="dropdown-menu dropdown-menu-right">
                                                        <a class="dropdown-item holiday-edit-trigger" href="#" data-toggle="modal" data-target="#edit_holiday" data-id="{{ $holiday->id }}" data-name="{{ $holiday->name_holiday }}" data-date="{{ $holiday->date_holiday }}"><i class="fa fa-pencil m-r-5"></i> Edit</a>
                                                        <a class="dropdown-item holiday-delete-trigger" href="#" data-toggle="modal" data-target="#delete_holiday" data-id="{{ $holiday->id }}" data-name="{{ $holiday->name_holiday }}"><i class="fa fa-trash-o m-r-5"></i> Delete</a>
                                                    </div>
                                                </div>
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ Auth::user()?->isAdmin() ? 6 : 5 }}" class="text-center table-empty">No holidays have been configured yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal custom-modal fade" id="add_holiday" role="dialog">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Holiday</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('employee/holidays/save') }}" method="POST">
                            @csrf
                            <div class="form-group">
                                <label>Holiday Name <span class="text-danger">*</span></label>
                                <input class="form-control" type="text" name="nameHoliday" value="{{ old('nameHoliday') }}">
                            </div>
                            <div class="form-group">
                                <label>Holiday Date <span class="text-danger">*</span></label>
                                <input class="form-control" type="date" name="holidayDate" value="{{ old('holidayDate') }}">
                            </div>
                            <div class="submit-section">
                                <button type="submit" class="btn btn-primary submit-btn">Save Holiday</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal custom-modal fade" id="edit_holiday" role="dialog">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Holiday</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('employee/holidays/update') }}" method="POST">
                            @csrf
                            <input type="hidden" name="id" id="holiday_edit_id">
                            <div class="form-group">
                                <label>Holiday Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="holiday_edit_name" name="holidayName">
                            </div>
                            <div class="form-group">
                                <label>Holiday Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="holiday_edit_date" name="holidayDate">
                            </div>
                            <div class="submit-section">
                                <button type="submit" class="btn btn-primary submit-btn">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal custom-modal fade" id="delete_holiday" role="dialog">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body">
                        <div class="form-header">
                            <h3>Delete Holiday</h3>
                            <p>Remove <strong id="holiday_delete_name"></strong> from the calendar?</p>
                        </div>
                        <form action="{{ route('employee/holidays/delete') }}" method="POST">
                            @csrf
                            <input type="hidden" name="id" id="holiday_delete_id">
                            <div class="modal-btn delete-action">
                                <div class="row">
                                    <div class="col-6">
                                        <button type="submit" class="btn btn-primary continue-btn">Delete</button>
                                    </div>
                                    <div class="col-6">
                                        <a href="javascript:void(0);" data-dismiss="modal" class="btn btn-primary cancel-btn">Cancel</a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @section('script')
    <script>
        $(document).on('click', '.holiday-edit-trigger', function () {
            $('#holiday_edit_id').val($(this).data('id'));
            $('#holiday_edit_name').val($(this).data('name'));
            $('#holiday_edit_date').val($(this).data('date'));
        });

        $(document).on('click', '.holiday-delete-trigger', function () {
            $('#holiday_delete_id').val($(this).data('id'));
            $('#holiday_delete_name').text($(this).data('name'));
        });
    </script>
    @endsection
@endsection
