@extends('layouts.app')
@section('content')
    <div class="main-wrapper">
        <div class="account-content">
            <div class="container">
                {!! Toastr::message() !!}
                <div class="brand-auth-shell">
                    <div class="brand-auth-panel">
                        <span class="brand-auth-kicker">Secure credential update</span>
                        <h1 class="brand-auth-title">Create a new password for your workspace.</h1>
                        <p class="brand-auth-copy">Use a strong password for your Purple HR account so payroll, employee records, and reporting access stay protected.</p>
                    </div>
                    <div class="brand-auth-card">
                        <a class="brand-auth-logo" href="{{ route('login') }}"><img src="{{ URL::to('assets/img/logo.png') }}" alt="Purple HR"></a>
                        <h3 class="account-title">Reset Password</h3>
                        <p class="account-subtitle">Choose a new password for your account.</p>
                        <form method="POST" action="/reset-password">
                            @csrf
                            <input type="hidden" name="token" value="{{ $token }}">
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" placeholder="name@company.com" autocomplete="email">
                                @error('email')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label>Password</label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror" name="password" placeholder="Enter new password" autocomplete="new-password">
                                @error('password')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label>Repeat Password</label>
                                <input type="password" class="form-control" name="password_confirmation" placeholder="Repeat new password" autocomplete="new-password">
                            </div>
                            <div class="form-group text-center mb-0">
                                <button class="btn btn-primary account-btn" type="submit">Update password</button>
                            </div>
                            <div class="brand-auth-footer">Already have access? <a href="{{ route('login') }}">Back to login</a></div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
