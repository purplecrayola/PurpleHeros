@extends('layouts.settings')
@section('content')
    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="row">
                <div class="col-md-8 offset-md-2 col-xl-6 offset-xl-3">
                    <div class="page-header">
                        <div class="row">
                            <div class="col-sm-12">
                                <h3 class="page-title">Change Password</h3>
                                <p class="text-muted mb-0">Update your account password securely.</p>
                            </div>
                        </div>
                    </div>

                    @include('settings.partials.settings-tabs', ['active' => 'password'])

                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <form method="POST" action="{{ route('change/password/db') }}">
                                @csrf
                                <div class="form-group mb-3">
                                    <label for="current_password">Current Password</label>
                                    <input id="current_password" type="password" class="form-control @error('current_password') is-invalid @enderror" name="current_password" autocomplete="current-password" required>
                                    @error('current_password')
                                        <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>

                                <div class="form-group mb-3">
                                    <label for="new_password">New Password</label>
                                    <input id="new_password" type="password" class="form-control @error('new_password') is-invalid @enderror" name="new_password" autocomplete="new-password" required>
                                    <small class="form-text text-muted">Use at least 8 characters.</small>
                                    @error('new_password')
                                        <span class="invalid-feedback d-block"><strong>{{ $message }}</strong></span>
                                    @enderror
                                </div>

                                <div class="form-group mb-4">
                                    <label for="new_password_confirmation">Confirm New Password</label>
                                    <input id="new_password_confirmation" type="password" class="form-control" name="new_password_confirmation" autocomplete="new-password" required>
                                </div>

                                <div class="submit-section mb-0">
                                    <button type="submit" class="btn btn-primary submit-btn">Update Password</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

