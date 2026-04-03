@extends('layouts.app')
@section('content')
    <div class="main-wrapper">
        <div class="account-content">
            <div class="container">
                {!! Toastr::message() !!}
                <div class="brand-auth-shell">
                    <div class="brand-auth-panel">
                        <span class="brand-auth-kicker">Account recovery</span>
                        <h1 class="brand-auth-title">Reset access without support delays.</h1>
                        <p class="brand-auth-copy">Enter the email attached to your Purple HR account and the system will send a password reset link.</p>
                    </div>
                    <div class="brand-auth-card">
                        <a class="brand-auth-logo" href="{{ route('login') }}"><img src="{{ URL::to('assets/img/logo.png') }}" alt="Purple HR"></a>
                        <h3 class="account-title">Forgot Password</h3>
                        <p class="account-subtitle">We will send a reset link to your registered email address.</p>
                        <form method="POST" action="/forget-password">
                            @csrf
                            <div class="form-group">
                                <label>Email Address</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" placeholder="name@company.com" autocomplete="email">
                                @error('email')
                                    <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                                @enderror
                            </div>
                            <div class="form-group text-center mb-0">
                                <button class="btn btn-primary account-btn" type="submit">Send reset link</button>
                            </div>
                            <div class="brand-auth-footer">Remembered your password? <a href="{{ route('login') }}">Back to login</a></div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
