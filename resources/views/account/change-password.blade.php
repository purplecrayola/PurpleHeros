@extends('layouts.master')
@section('content')
    @php($todayLabel = \Carbon\Carbon::now()->format('l, M j'))
    <style>
        .employee-password-modern .password-identity-day {
            color: #5e5873;
            font-size: 16px;
            line-height: 24px;
            margin-bottom: 8px;
        }
        .employee-password-modern .password-content-shell {
            max-width: 860px;
            margin: 0 auto;
        }
        .employee-password-modern .password-identity-title {
            color: #171327;
            font-family: "Playfair Display", serif;
            font-size: 56px;
            line-height: 1.04;
            letter-spacing: -0.02em;
            font-weight: 700;
            margin: 0 0 12px;
        }
        .employee-password-modern .password-identity-subtitle {
            color: #5e5873;
            font-size: 16px;
            line-height: 24px;
            max-width: 620px;
            margin: 0;
        }
        .employee-password-modern .password-quick-nav {
            border: 1px solid #eae7f2;
            border-radius: 16px;
            background: #ffffff;
            padding: 16px 20px;
            margin-bottom: 24px;
        }
        .employee-password-modern .password-quick-nav-label {
            color: #5e5873;
            font-size: 12px;
            line-height: 16px;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 10px;
        }
        .employee-password-modern .password-quick-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .employee-password-modern .password-chip {
            border: 1px solid #d9d4e5;
            border-radius: 999px;
            background: #ffffff;
            color: #5e5873;
            text-decoration: none;
            font-size: 15px;
            line-height: 22px;
            font-weight: 500;
            padding: 8px 14px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .employee-password-modern .password-chip:hover {
            color: #6d28d9;
            border-color: #6d28d9;
            background: #f7f2ff;
            text-decoration: none;
        }
        .employee-password-modern .password-chip.active {
            color: #6d28d9;
            border-color: #e0d6f7;
            background: #f7f2ff;
        }
        .employee-password-modern .password-form-card {
            border: 1px solid #eae7f2;
            border-radius: 16px;
            background: #ffffff;
            box-shadow: none;
        }
        .employee-password-modern .password-form-card .card-body {
            padding: 20px;
        }
        @media (max-width: 991px) {
            .employee-password-modern .password-identity-title {
                font-size: 42px;
                line-height: 1.08;
            }
        }
        @media (max-width: 767px) {
            .employee-password-modern .password-identity-title {
                font-size: 36px;
            }
            .employee-password-modern .password-form-card .card-body {
                padding: 16px;
            }
        }
    </style>
    <div class="page-wrapper employee-password-modern">
        <div class="content container-fluid">
            @include('employees.partials.employee-topbar', ['context' => 'Security workspace'])

            <div class="password-content-shell">
                <div class="mb-4">
                    <p class="password-identity-day">{{ $todayLabel }}</p>
                    <h1 class="password-identity-title">Change Password</h1>
                    <p class="password-identity-subtitle">Update your account password securely from your self-service workspace.</p>
                </div>

                <div class="row">
                    <div class="col-12">
                    <div class="password-quick-nav">
                        <p class="password-quick-nav-label mb-0">Account</p>
                        <div class="password-quick-actions mt-2">
                            <a href="{{ route('profile_user') }}" class="password-chip">
                                <i class="la la-user"></i>
                                <span>My Profile</span>
                            </a>
                        </div>
                    </div>

                    <div class="password-quick-nav">
                        <p class="password-quick-nav-label mb-0">Security</p>
                        <div class="password-quick-actions mt-2">
                            <a href="{{ route('change/password') }}" class="password-chip active">
                                <i class="la la-lock"></i>
                                <span>Change Password</span>
                            </a>
                        </div>
                    </div>

                    <div class="card password-form-card">
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
    </div>
@endsection
