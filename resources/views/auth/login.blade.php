@extends('layouts.app')
@section('title', \App\Models\CompanySettings::current()->login_page_title ?: 'PurpleHeros Access')
@section('content')
    @php($brandSettings = \App\Models\CompanySettings::current())
    @php($brandName = $brandSettings->login_brand_label ?: 'PurpleHeros')
    @php($loginLogo = $brandSettings->assetUrl('login_logo_path', 'assets/img/brand/purplecrayola-black.svg'))
    @php($loginImage = $brandSettings->assetUrl('login_image_path', 'assets/img/brand/login-background.jpg'))
    @php($slogan = $brandSettings->login_hero_copy ?: 'Digital systems. Designed to work.')
    @php($peopleOpsEmail = trim((string) ($brandSettings->people_ops_email ?: $brandSettings->mail_reply_to_address ?: $brandSettings->email ?: 'heros@purplecrayola.com')))
    <div class="main-wrapper">
        <div class="account-content pc-login-shell">
            {!! Toastr::message() !!}
            <div class="pc-login-wrap">
                <section class="pc-login-hero">
                    <div class="pc-login-hero-overlay"></div>
                    <img src="{{ $loginImage }}" alt="{{ $brandName }}" class="pc-login-hero-image" />
                    <div class="pc-login-hero-content">
                        <span class="pc-login-kicker">{{ $brandName }}</span>
                        <h1>{{ $slogan }}</h1>
                    </div>
                </section>

                <section class="pc-login-panel">
                    <div class="pc-login-panel-head">
                        <div>
                            <h2>Employee Sign In</h2>
                            <p>{{ $brandSettings->login_right_copy ?: 'Access your workspace using your official credentials.' }}</p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('login') }}" class="pc-login-form">
                        @csrf
                        <div class="form-group">
                            <label class="pc-login-label">Email or User ID</label>
                            <div class="pc-login-input-wrap">
                                <span class="pc-login-input-icon"><i class="la la-user"></i></span>
                                <input type="text" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" placeholder="you@company.com" autocomplete="username">
                            </div>
                            @error('email')
                                <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label class="pc-login-label">Password</label>
                            <div class="pc-login-input-wrap">
                                <span class="pc-login-input-icon"><i class="la la-lock"></i></span>
                                <input id="pc-login-password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" placeholder="Enter your password" autocomplete="current-password">
                                <button class="pc-login-password-toggle" type="button" aria-label="Show password">
                                    <i class="la la-eye"></i>
                                </button>
                            </div>
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

                    <div class="pc-login-divider"><span>OR</span></div>

                    <a class="pc-login-support" href="mailto:{{ filter_var($peopleOpsEmail, FILTER_VALIDATE_EMAIL) ? $peopleOpsEmail : 'heros@purplecrayola.com' }}">
                        <span class="pc-login-support-icon"><i class="la la-headphones"></i></span>
                        <span>Need support? <strong>Contact People Ops</strong></span>
                        <i class="la la-arrow-right"></i>
                    </a>

                    <div class="pc-login-footnote">
                        <span class="pc-login-footnote-brand">
                            <img src="{{ $loginLogo }}" alt="{{ $brandName }}">
                            <span>{{ $brandSettings->login_help_line_two ?: $brandName }}</span>
                        </span>
                        <span>{{ $brandSettings->login_help_line_three ?: 'Purple Crayola Employee Access' }}</span>
                    </div>
                </section>
            </div>
        </div>
    </div>
@endsection

@section('script')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var passwordInput = document.getElementById('pc-login-password');
        var toggleButton = document.querySelector('.pc-login-password-toggle');

        if (!passwordInput || !toggleButton) {
            return;
        }

        toggleButton.addEventListener('click', function () {
            var showing = passwordInput.type === 'text';
            passwordInput.type = showing ? 'password' : 'text';
            toggleButton.setAttribute('aria-label', showing ? 'Show password' : 'Hide password');
            var icon = toggleButton.querySelector('i');
            if (icon) {
                icon.className = showing ? 'la la-eye' : 'la la-eye-slash';
            }
        });
    });
</script>
@endsection
