@extends('layouts.settings')
@section('style')
    <link rel="stylesheet" href="{{ URL::to('assets/css/checkbox-style.css') }}">
@endsection
@section('content')
    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">Roles</h3>
                        <p class="text-muted mb-0">Manage the role catalog used by user accounts. Fine-grained module permissions are not configurable yet in this edition.</p>
                    </div>
                </div>
            </div>
            @include('settings.partials.settings-tabs', ['active' => 'roles'])

            {!! Toastr::message() !!}

            <div class="row">
                <div class="col-md-4">
                    <div class="card dash-widget">
                        <div class="card-body">
                            <span class="dash-widget-icon"><i class="la la-key"></i></span>
                            <div class="dash-widget-info">
                                <h3>{{ $summary['roles_count'] }}</h3>
                                <span>Configured Roles</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card dash-widget">
                        <div class="card-body">
                            <span class="dash-widget-icon"><i class="la la-users"></i></span>
                            <div class="dash-widget-info">
                                <h3>{{ $summary['assigned_users'] }}</h3>
                                <span>User Assignments</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card dash-widget">
                        <div class="card-body">
                            <span class="dash-widget-icon"><i class="la la-warning"></i></span>
                            <div class="dash-widget-info">
                                <h3>{{ $summary['unassigned_roles'] }}</h3>
                                <span>Unused Roles</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-4 col-xl-4">
                    <a href="#" class="btn btn-primary btn-block mb-3" data-toggle="modal" data-target="#add_role"><i class="fa fa-plus"></i> Add Role</a>
                    <div class="roles-menu">
                        <ul>
                            @forelse ($rolesPermissions as $role)
                                <li data-id="{{ $role->id }}" data-role-name="{{ $role->role_type }}" data-role-users="{{ $role->assigned_users }}" class="{{ $loop->first ? 'active' : '' }}">
                                    <a href="javascript:void(0);">
                                        <span>
                                            <span class="roleName">{{ $role->role_type }}</span>
                                            <small class="d-block text-muted">{{ $role->assigned_users }} {{ \Illuminate\Support\Str::plural('user', $role->assigned_users) }}</small>
                                        </span>
                                        <span class="role-action">
                                            <span class="action-circle large rolesUpdate" data-toggle="modal" data-target="#edit_role">
                                                <i class="material-icons">edit</i>
                                            </span>
                                            <span class="action-circle large delete-btn rolesDelete" data-toggle="modal" data-target="#delete_role">
                                                <i class="material-icons">delete</i>
                                            </span>
                                        </span>
                                    </a>
                                </li>
                            @empty
                                <li class="active"><a href="javascript:void(0);"><span>No roles have been configured yet.</span></a></li>
                            @endforelse
                        </ul>
                    </div>
                </div>
                <div class="col-lg-8 col-xl-8">
                    <div class="card">
                        <div class="card-header border-0 pb-0">
                            <h5 class="card-title">Role Guidance</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-4">
                                <h6 class="mb-2">What this page controls</h6>
                                <ul class="mb-0">
                                    <li>Role labels available during user creation and user updates</li>
                                    <li>The role names shown in employee, payroll, and user management screens</li>
                                    <li>Basic role cleanup for SMB deployments</li>
                                </ul>
                            </div>
                            <div class="mb-4">
                                <h6 class="mb-2">What this page does not control yet</h6>
                                <ul class="mb-0">
                                    <li>Field-level permissions</li>
                                    <li>Module-by-module access rules</li>
                                    <li>Approval routing or workflow permissions</li>
                                </ul>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-striped custom-table mb-0">
                                    <thead>
                                        <tr>
                                            <th>Role</th>
                                            <th>Assigned Users</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($rolesPermissions as $role)
                                            <tr>
                                                <td>{{ $role->role_type }}</td>
                                                <td>{{ $role->assigned_users }}</td>
                                                <td>
                                                    @if ($role->assigned_users > 0)
                                                        <span class="badge bg-inverse-success">In Use</span>
                                                    @else
                                                        <span class="badge bg-inverse-warning">Unused</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-center text-muted">No roles available.</td>
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

        <div id="add_role" class="modal custom-modal fade" role="dialog">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Role</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="validation_role" action="{{ route('roles/permissions/save') }}" method="POST">
                            @csrf
                            <div class="form-group">
                                <label>Role Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('roleName') is-invalid @enderror" id="roleName" name="roleName" value="{{ old('roleName') }}" placeholder="e.g. HR Manager">
                                @error('roleName')<span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>@enderror
                            </div>
                            <div class="submit-section">
                                <button type="submit" class="btn btn-primary submit-btn">Save Role</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div id="edit_role" class="modal custom-modal fade" role="dialog">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content modal-md">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Role</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('roles/permissions/update') }}" method="POST">
                            @csrf
                            <div class="form-group">
                                <input type="hidden" name="id" id="e_id" value="">
                                <label>Role Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="e_roleName" name="roleName" value="">
                            </div>
                            <div class="submit-section">
                                <button type="submit" class="btn btn-primary submit-btn">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal custom-modal fade" id="delete_role" role="dialog">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body">
                        <div class="form-header">
                            <h3>Delete Role</h3>
                            <p class="delete-message">Are you sure you want to delete this role?</p>
                        </div>
                        <div class="modal-btn delete-action">
                            <form action="{{ route('roles/permissions/delete') }}" method="POST">
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
            $(document).on('click', '.rolesUpdate', function() {
                var item = $(this).closest('li');
                $('#e_id').val(item.data('id'));
                $('#e_roleName').val(item.data('role-name'));
            });
        </script>
        <script>
            $(document).on('click', '.rolesDelete', function() {
                var item = $(this).closest('li');
                $('.e_id').val(item.data('id'));
                var assignedUsers = parseInt(item.data('role-users'), 10) || 0;
                var roleName = item.data('role-name');
                $('.delete-message').text(
                    assignedUsers > 0
                        ? '"' + roleName + '" is assigned to ' + assignedUsers + ' users. Remove those assignments before deleting this role.'
                        : 'Are you sure you want to delete "' + roleName + '"?'
                );
            });
        </script>
        <script>
            $('#validation_role').validate({
                rules: {
                    roleName: 'required',
                },
                messages: {
                    roleName: 'Please enter a role name.',
                },
                submitHandler: function(form) {
                    form.submit();
                }
            });
        </script>
    @endsection
@endsection
