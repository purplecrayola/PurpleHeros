@extends('layouts.app')
@section('title', \App\Models\CompanySettings::current()->login_page_title ?: 'PurpleHeros Access')
@section('content')
    @php($brandSettings = \App\Models\CompanySettings::current())
    @php($brandName = $brandSettings->login_brand_label ?: 'PurpleHeros')
    @php($loginLogo = $brandSettings->assetUrl('login_logo_path', 'assets/img/brand/purplecrayola-black.svg'))
    @php($loginImage = $brandSettings->assetUrl('login_image_path', 'assets/images/purplecrayola heros login.jpg'))
    @php($slogan = $brandSettings->login_hero_copy ?: 'Digital systems. Designed to work.')
    <div class="main-wrapper">
        <div class="account-content pc-login-shell">
            {!! Toastr::message() !!}
            <div class="pc-login-wrap">
                <section class="pc-login-hero">
                    <div class="pc-login-hero-overlay"></div>
                    <img src="{{ $loginImage }}" alt="{{ $brandName }}" class="pc-login-hero-image" />
                    <div class="pc-login-hero-content">
                        <span class="pc-login-kicker">{{ $brandName }}</span>
                        <h1>{{ $brandName }}</h1>
                        <p class="pc-login-slogan">{{ $slogan }}</p>
                    </div>
                </section>

                <section class="pc-login-panel">
                    <span class="d-none">Sign in to PurpleHeros</span>
                    <div class="pc-login-panel-head">
                        <div class="pc-login-logo-tile">
                            <img src="{{ $loginLogo }}" alt="{{ $brandName }}">
                        </div>
                        <div>
                            <h2>Employee Sign In</h2>
                            <p>{{ $brandSettings->login_right_copy ?: 'Use your issued work credentials. Contact HR for support.' }}</p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('login') }}" class="pc-login-form">
                        @csrf
                        <div class="form-group">
                            <label class="pc-login-label">Email or User ID</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" placeholder="you@company.com" autocomplete="email">
                            @error('email')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                        <div class="form-group mb-2">
                            <label class="pc-login-label">Password</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" name="password" placeholder="Enter your password" autocomplete="current-password">
                            @error('password')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                        <div class="pc-login-links-row">
                            <a href="{{ route('forget-password') }}">{{ $brandSettings->login_help_line_one ?: 'Forgot password?' }}</a>
                        </div>
                        <div class="form-group text-center mb-0">
                            <button class="btn btn-primary account-btn pc-login-submit" type="submit">Sign in</button>
                        </div>
                    </form>

                    <div class="pc-login-footnote">
                        <span>{{ $brandSettings->login_help_line_two ?: $brandName }}</span>
                        <span>{{ $brandSettings->login_help_line_three ?: 'Purple Crayola Employee Access' }}</span>
                    </div>
                </section>
            </div>
        </div>
    </div>
@endsection
