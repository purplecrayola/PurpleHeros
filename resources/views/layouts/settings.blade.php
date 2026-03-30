<!DOCTYPE html>
<html lang="en">
@php($brandSettings = \App\Models\CompanySettings::current())
@php($brandName = $brandSettings->company_name ?: 'Purple HR')
@php($settingsRoute = Auth::user()->isAdmin() ? url('/admin/company-settings') : route('change/password'))
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <meta name="description" content="{{ $brandName }} settings and administration console.">
    <meta name="keywords" content="purple hr, settings, company profile, roles">
    <meta name="author" content="Purple Crayola">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $brandName }} Settings</title>
    <link rel="shortcut icon" type="image/x-icon" href="{{ $brandSettings->assetUrl('favicon_path', 'assets/img/favicon.ico') }}">
    <link rel="stylesheet" href="{{ URL::to('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ URL::to('assets/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ URL::to('assets/css/line-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ URL::to('assets/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ URL::to('assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ URL::to('assets/css/purple-brand.css') }}">
    <link rel="stylesheet" href="{{ URL::to('assets/css/toastr.min.css') }}">
    <script src="{{ URL::to('assets/js/toastr_jquery.min.js') }}"></script>
    <script src="{{ URL::to('assets/js/toastr.min.js') }}"></script>
    <style>
        :root {
            --pc-primary: {{ $brandSettings->color('brand_primary_color', '#8000F9') }};
            --pc-primary-rgb: {{ $brandSettings->colorRgb('brand_primary_color', '#8000F9') }};
            --pc-dark: {{ $brandSettings->color('brand_dark_color', '#021530') }};
            --pc-dark-rgb: {{ $brandSettings->colorRgb('brand_dark_color', '#021530') }};
            --pc-neutral: {{ $brandSettings->color('brand_neutral_color', '#DCDDDF') }};
            --pc-header-text: {{ $brandSettings->color('header_text_color', '#FFFFFF') }};
            --pc-sidebar-text: {{ $brandSettings->color('sidebar_text_color', '#F5F7FF') }};
            --pc-sidebar-muted: {{ $brandSettings->color('sidebar_muted_text_color', '#A9B8CC') }};
            --pc-login-image: url('{{ $brandSettings->assetUrl('login_image_path', 'assets/img/brand/login-background.jpg') }}');
        }
    </style>
</head>
@yield('style')
<style>.error{color:red;}</style>
<style>
    .settings-tab-groups {
        display: grid;
        gap: 12px;
    }
    .settings-tab-group {
        background: #fff;
        border: 1px solid rgba(var(--pc-dark-rgb), 0.08);
        border-radius: 12px;
        padding: 12px;
    }
    .settings-tab-group__label {
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: rgba(var(--pc-dark-rgb), 0.62);
        margin-bottom: 8px;
    }
    .settings-tab-nav {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }
    .settings-tab-nav .nav-link {
        border-radius: 999px;
        border: 1px solid rgba(var(--pc-primary-rgb), 0.2);
        color: rgba(var(--pc-dark-rgb), 0.88);
        background: rgba(var(--pc-primary-rgb), 0.05);
        font-weight: 600;
        padding: 8px 14px;
    }
    .settings-tab-nav .nav-link:hover {
        border-color: rgba(var(--pc-primary-rgb), 0.45);
        background: rgba(var(--pc-primary-rgb), 0.12);
    }
    .settings-tab-nav .nav-link.active {
        border-color: transparent;
        color: #fff;
        background: linear-gradient(135deg, var(--pc-primary), rgba(var(--pc-dark-rgb), 0.95));
        box-shadow: 0 8px 20px rgba(var(--pc-primary-rgb), 0.25);
    }
</style>
<body>
<div class="main-wrapper">
    <div class="header">
        <div class="header-left">
            <a href="{{ Auth::user()->isAdmin() ? url('/admin') : route('em/dashboard') }}" class="logo">
                <img src="{{ $brandSettings->assetUrl('header_logo_path', 'assets/img/brand/purplecrayola-white.svg') }}" alt="{{ $brandName }}">
            </a>
        </div>
        <a id="toggle_btn" href="javascript:void(0);"><span class="bar-icon"><span></span><span></span><span></span></span></a>
        <div class="page-title-box"><h3>{{ $brandName }} Settings</h3></div>
        <a id="mobile_btn" class="mobile_btn" href="#sidebar"><i class="fa fa-bars"></i></a>
        <ul class="nav user-menu">
            <li class="nav-item">
                <div class="top-nav-search">
                    <a href="javascript:void(0);" class="responsive-search"><i class="fa fa-search"></i></a>
                    <form action="{{ route('global/search') }}" method="GET" aria-label="Global search">
                        <input class="form-control" type="text" name="q" value="{{ request('q') }}" placeholder="Search people or pages (e.g. Joy, attendance, payslips)">
                        <button class="btn" type="submit" aria-label="Search"><i class="fa fa-search"></i></button>
                    </form>
                </div>
            </li>
            <li class="nav-item dropdown has-arrow flag-nav">
                <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" role="button">
                    <img src="{{ URL::to('assets/img/flags/us.png') }}" alt="English" height="20"> <span>English</span>
                </a>
                <div class="dropdown-menu dropdown-menu-right">
                    <a href="javascript:void(0);" class="dropdown-item"><img src="{{ URL::to('assets/img/flags/us.png') }}" alt="English" height="16"> English</a>
                </div>
            </li>
            <li class="nav-item dropdown has-arrow main-drop">
                <a href="#" class="dropdown-toggle nav-link" data-toggle="dropdown">
                    <span class="user-img"><img src="{{ Auth::user()->avatar_url }}" alt="{{ Auth::user()->name }}"><span class="status online"></span></span>
                    <span>{{ Auth::user()->name }}</span>
                </a>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="{{ route('profile_user') }}">My Profile</a>
                    <a class="dropdown-item" href="{{ $settingsRoute }}">Settings</a>
                    <a class="dropdown-item" href="{{ route('change/password') }}">Change Password</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item">Logout</button>
                    </form>
                </div>
            </li>
        </ul>
    </div>
    @include('sidebar.sidebarsetting')
    @yield('content')
</div>
<script src="{{ URL::to('assets/js/jquery-3.5.1.min.js') }}"></script>
<script src="{{ URL::to('assets/js/popper.min.js') }}"></script>
<script src="{{ URL::to('assets/js/bootstrap.min.js') }}"></script>
<script src="{{ URL::to('assets/js/jquery.slimscroll.min.js') }}"></script>
<script src="{{ URL::to('assets/js/select2.min.js') }}"></script>
<script src="{{ URL::to('assets/js/jquery.validate.js') }}"></script>
<script src="{{ URL::to('assets/js/app.js') }}"></script>
@yield('script')
</body>
</html>
