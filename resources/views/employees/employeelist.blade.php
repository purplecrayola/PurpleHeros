@extends('layouts.master')
@section('content')
    @section('style')
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.3/css/all.css" >
    <link rel="stylesheet" href="{{ URL::to('assets/css/checkbox-style.css') }}">
    @endsection
    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">Employee</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Employee</li>
                        </ul>
                    </div>
                    <div class="col-auto float-right ml-auto">
                        <a href="#" class="btn add-btn" data-toggle="modal" data-target="#add_employee"><i class="fa fa-plus"></i> Add Employee</a>
                        <div class="view-icons">
                            <a href="{{ route('all/employee/card') }}" class="grid-view btn btn-link active"><i class="fa fa-th"></i></a>
                            <a href="{{ route('all/employee/list') }}" class="list-view btn btn-link"><i class="fa fa-bars"></i></a>
                        </div>
                    </div>
                </div>
            </div>

            <form action="{{ route('all/employee/list/search') }}" method="POST">
                @csrf
                <div class="row filter-row">
                    <div class="col-sm-6 col-md-3">
                        <div class="form-group form-focus">
                            <input type="text" class="form-control floating" name="employee_id">
                            <label class="focus-label">Employee ID</label>
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <div class="form-group form-focus">
                            <input type="text" class="form-control floating" name="name">
                            <label class="focus-label">Employee Name</label>
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <div class="form-group form-focus">
                            <input type="text" class="form-control floating" name="position">
                            <label class="focus-label">Position</label>
                        </div>
                    </div>
                    <div class="col-sm-6 col-md-3">
                        <button type="submit" class="btn btn-success btn-block"> Search </button>
                    </div>
                </div>
            </form>

            {!! Toastr::message() !!}

            <div class="row">
                <div class="col-md-12">
                    <div class="table-responsive">
                        <table class="table table-striped custom-table datatable">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Employee ID</th>
                                    <th>Email</th>
                                    <th>Mobile</th>
                                    <th class="text-nowrap">Join Date</th>
                                    <th>Role</th>
                                    <th class="text-right no-sort">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($users as $items )
                                <tr>
                                    <td>
                                        <h2 class="table-avatar">
                                            <a href="{{ url('employee/profile/'.$items->user_id) }}" class="avatar"><img alt="" src="{{ \App\Support\MediaStorageManager::publicUrl($items->avatar ?? null, 'assets/img/user.jpg', 'assets/images') }}"></a>
                                            <a href="{{ url('employee/profile/'.$items->user_id) }}">{{ $items->name }}<span>{{ $items->position }}</span></a>
                                        </h2>
                                    </td>
                                    <td>{{ $items->user_id }}</td>
                                    <td>{{ $items->email }}</td>
                                    <td>{{ $items->phone_number }}</td>
                                    <td>{{ $items->join_date }}</td>
                                    <td>{{ $items->role_name }}</td>
                                    <td class="text-right">
                                        <div class="dropdown dropdown-action">
                                            <a href="#" class="action-icon dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><i class="material-icons">more_vert</i></a>
                                            <div class="dropdown-menu dropdown-menu-right">
                                                <a class="dropdown-item" href="{{ url('all/employee/view/edit/'.$items->user_id) }}"><i class="fa fa-pencil m-r-5"></i> Edit</a>
                                                <form action="{{ route('all/employee/delete', $items->user_id) }}" method="POST" onsubmit="return confirm('Are you sure to want to delete it?');">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item"><i class="fa fa-trash-o m-r-5"></i> Delete</button>
                                                </form>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div id="add_employee" class="modal custom-modal fade" role="dialog">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Employee</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('all/employee/save') }}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label class="col-form-label">Full Name</label>
                                        <select class="select" id="name" name="name">
                                            <option value="">-- Select --</option>
                                            @foreach ($userList as $key=>$user )
                                                <option value="{{ $user->name }}" data-employee_id={{ $user->user_id }} data-email={{ $user->email }}>{{ $user->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label class="col-form-label">Email <span class="text-danger">*</span></label>
                                        <input class="form-control" type="email" id="email" name="email" placeholder="Auto email" readonly>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Birth Date</label>
                                        <div class="cal-icon">
                                            <input class="form-control datetimepicker" type="text" id="birthDate" name="birthDate">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Gender</label>
                                        <select class="select form-control" id="gender" name="gender">
                                            <option value="Male">Male</option>
                                            <option value="Female">Female</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label class="col-form-label">Employee ID <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="employee_id" name="employee_id" placeholder="Auto id employee" readonly>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label class="col-form-label">Line Manager</label>
                                        <select class="select" id="company" name="company">
                                            <option value="">-- Select --</option>
                                            @foreach ($userList as $key=>$user )
                                                <option value="{{ $user->name }}">{{ $user->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="table-responsive m-t-15">
                                <table class="table table-striped custom-table">
                                    <thead>
                                        <tr>
                                            <th>Module Permission</th>
                                            <th class="text-center">Read</th>
                                            <th class="text-center">Write</th>
                                            <th class="text-center">Create</th>
                                            <th class="text-center">Delete</th>
                                            <th class="text-center">Import</th>
                                            <th class="text-center">Export</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $key = 0; $key1 = 0; ?>
                                        @foreach ($permission_lists as $lists )
                                        <tr>
                                            <td>{{ $lists->permission_name }}</td>
                                            <input type="hidden" name="permission[]" value="{{ $lists->permission_name }}">
                                            <input type="hidden" name="id_count[]" value="{{ $lists->id }}">
                                            <td class="text-center">
                                                <input type="checkbox" class="option-input checkbox read{{ ++$key }}" id="read" name="read[]" value="Y"{{ $lists->read =="Y" ? 'checked' : ''}} >
                                                <input type="checkbox" class="option-input checkbox read{{ ++$key1 }}" id="read" name="read[]" value="N" {{ $lists->read =="N" ? 'checked' : ''}}>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" class="option-input checkbox write{{ ++$key }}" id="write" name="write[]" value="Y" {{ $lists->write =="Y" ? 'checked' : ''}}>
                                                <input type="checkbox" class="option-input checkbox write{{ ++$key1 }}" id="write" name="write[]" value="N" {{ $lists->write =="N" ? 'checked' : ''}}>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" class="option-input checkbox create{{ ++$key }}" id="create" name="create[]" value="Y" {{ $lists->create =="Y" ? 'checked' : ''}}>
                                                <input type="checkbox" class="option-input checkbox create{{ ++$key1 }}" id="create" name="create[]" value="N" {{ $lists->create =="N" ? 'checked' : ''}}>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" class="option-input checkbox delete{{ ++$key }}" id="delete" name="delete[]" value="Y" {{ $lists->delete =="Y" ? 'checked' : ''}}>
                                                <input type="checkbox" class="option-input checkbox delete{{ ++$key1 }}" id="delete" name="delete[]" value="N" {{ $lists->delete =="N" ? 'checked' : ''}}>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" class="option-input checkbox import{{ ++$key }}" id="import" name="import[]" value="Y" {{ $lists->import =="Y" ? 'checked' : ''}}>
                                                <input type="checkbox" class="option-input checkbox import{{ ++$key1 }}" id="import" name="import[]" value="N" {{ $lists->import =="N" ? 'checked' : ''}}>
                                            </td>
                                            <td class="text-center">
                                                <input type="checkbox" class="option-input checkbox export{{ ++$key }}" id="export" name="export[]" value="Y" {{ $lists->export =="Y" ? 'checked' : ''}}>
                                                <input type="checkbox" class="option-input checkbox export{{ ++$key1 }}" id="export" name="export[]" value="N" {{ $lists->export =="N" ? 'checked' : ''}}>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="submit-section">
                                <button class="btn btn-primary submit-btn">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @section('script')
    <script>
        $('#name').on('change', function() {
            $('#employee_id').val($(this).find(':selected').data('employee_id') || '');
            $('#email').val($(this).find(':selected').data('email') || '');
        });

        $('input:checkbox').on('click', function() {
            var $box = $(this);
            if ($box.is(':checked')) {
                var group = "input:checkbox[class='" + $box.attr('class') + "']";
                $(group).prop('checked', false);
                $box.prop('checked', true);
            } else {
                $box.prop('checked', false);
            }
        });
    </script>
    @endsection
@endsection
