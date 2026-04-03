<!DOCTYPE html>
<html lang="en">
@php($brandSettings = \App\Models\CompanySettings::current())
@php($brandName = $brandSettings->company_name ?: 'Purple HR')
@php($settingsRoute = Auth::user()->isAdmin() ? url('/admin/company-settings') : route('change/password'))
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <meta name="description" content="{{ $brandName }} mid-tier workforce management platform for employees, leave, attendance, payroll, and operations.">
    <meta name="keywords" content="hr software, payroll, attendance, leave management, employee management, purple hr">
    <meta name="author" content="Purple Crayola">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $brandName }}</title>
    <link rel="shortcut icon" type="image/x-icon" href="{{ $brandSettings->assetUrl('favicon_path', 'assets/img/favicon.ico') }}">
    <link rel="stylesheet" href="{{ URL::to('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ URL::to('assets/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ URL::to('assets/css/line-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ URL::to('assets/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ URL::to('assets/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ URL::to('assets/css/bootstrap-datetimepicker.min.css') }}">
    <link rel="stylesheet" href="{{ URL::to('assets/plugins/morris/morris.css') }}">
    <link rel="stylesheet" href="{{ URL::to('assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ URL::to('assets/css/purple-brand.css') }}">
    <link rel="stylesheet" href="{{ URL::to('assets/css/toastr.min.css') }}">
    <script src="{{ URL::to('assets/js/toastr_jquery.min.js') }}"></script>
    <script src="{{ URL::to('assets/js/toastr.min.js') }}"></script>
</head>

<body>
    @yield('style')
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
            --pc-workflow-current: {{ $brandSettings->color('workflow_current_color', '#8A00FF') }};
            --pc-workflow-current-rgb: {{ $brandSettings->colorRgb('workflow_current_color', '#8A00FF') }};
            --pc-workflow-completed: {{ $brandSettings->color('workflow_completed_color', '#0F9D58') }};
            --pc-workflow-completed-rgb: {{ $brandSettings->colorRgb('workflow_completed_color', '#0F9D58') }};
            --pc-workflow-pending: {{ $brandSettings->color('workflow_pending_color', '#DCDDDF') }};
            --pc-workflow-pending-rgb: {{ $brandSettings->colorRgb('workflow_pending_color', '#DCDDDF') }};
            --pc-login-image: url('{{ $brandSettings->assetUrl('login_image_path', 'assets/img/brand/login-background.jpg') }}');
            --ax-primary: {{ $brandSettings->color('brand_primary_color', '#8000F9') }};
            --ax-dark: {{ $brandSettings->color('brand_dark_color', '#021530') }};
            --ax-neutral: {{ $brandSettings->color('brand_neutral_color', '#DCDDDF') }};
            --ax-sidebar-text: {{ $brandSettings->color('sidebar_text_color', '#F5F7FF') }};
            --ax-sidebar-muted: {{ $brandSettings->color('sidebar_muted_text_color', '#A9B8CC') }};
        }
        .invalid-feedback { font-size: 14px; }
        .error { color: red; }
    </style>
    <div class="main-wrapper">
        <div id="loader-wrapper">
            <div id="loader">
                <div class="loader-ellips">
                    <span class="loader-ellips__dot"></span>
                    <span class="loader-ellips__dot"></span>
                    <span class="loader-ellips__dot"></span>
                    <span class="loader-ellips__dot"></span>
                </div>
            </div>
        </div>

        <div class="header">
            <div class="header-left">
                <a href="{{ Auth::user()->isAdmin() ? url('/admin') : route('em/dashboard') }}" class="logo">
                    <img src="{{ $brandSettings->assetUrl('header_logo_path', 'assets/img/brand/purplecrayola-white.svg') }}" alt="{{ $brandName }}">
                </a>
            </div>
            <a id="toggle_btn" href="javascript:void(0);">
                <span class="bar-icon"><span></span><span></span><span></span></span>
            </a>
            <div class="page-title-box">
                <h3>{{ $brandName }} | {{ Session::get('name') }}</h3>
            </div>
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
                <li class="nav-item dropdown">
                    <a href="#" class="dropdown-toggle nav-link" data-toggle="dropdown">
                        <i class="fa fa-bell-o"></i>
                        <span class="badge badge-pill">0</span>
                    </a>
                    <div class="dropdown-menu notifications">
                        <div class="topnav-dropdown-header">
                            <span class="notification-title">Notifications</span>
                            <a href="javascript:void(0)" class="clear-noti">Clear All</a>
                        </div>
                        <div class="noti-content">
                            <ul class="notification-list">
                                <li class="notification-message px-3 py-3 text-muted">No live notification feed is configured yet.</li>
                            </ul>
                        </div>
                    </div>
                </li>
                <li class="nav-item dropdown">
                    <a href="#" class="dropdown-toggle nav-link" data-toggle="dropdown">
                        <i class="fa fa-comment-o"></i> <span class="badge badge-pill">0</span>
                    </a>
                    <div class="dropdown-menu notifications">
                        <div class="topnav-dropdown-header">
                            <span class="notification-title">Messages</span>
                            <a href="javascript:void(0)" class="clear-noti">Clear All</a>
                        </div>
                        <div class="noti-content">
                            <ul class="notification-list">
                                <li class="notification-message px-3 py-3 text-muted">Internal messaging is not enabled in this edition.</li>
                            </ul>
                        </div>
                    </div>
                </li>
                <li class="nav-item dropdown has-arrow main-drop">
                    <a href="#" class="dropdown-toggle nav-link" data-toggle="dropdown">
                        <span class="user-img">
                            <img src="{{ Auth::user()->avatar_url }}" alt="{{ Auth::user()->name }}">
                            <span class="status online"></span>
                        </span>
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

        @include('sidebar.sidebar')
        @yield('content')
    </div>

    <script src="{{ URL::to('assets/js/jquery-3.5.1.min.js') }}"></script>
    <script src="{{ URL::to('assets/js/popper.min.js') }}"></script>
    <script src="{{ URL::to('assets/js/bootstrap.min.js') }}"></script>
    <script src="{{ URL::to('assets/js/jquery.slimscroll.min.js') }}"></script>
    <script src="{{ URL::to('assets/js/select2.min.js') }}"></script>
    <script src="{{ URL::to('assets/js/moment.min.js') }}"></script>
    <script src="{{ URL::to('assets/js/bootstrap-datetimepicker.min.js') }}"></script>
    <script src="{{ URL::to('assets/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ URL::to('assets/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ URL::to('assets/js/jquery.validate.js') }}"></script>
    <script src="{{ URL::to('assets/js/chart.js') }}"></script>
    <script src="{{ URL::to('assets/js/line-chart.js') }}"></script>
    <script src="{{ URL::to('assets/js/app.js') }}"></script>
    @yield('script')
</body>
</html>
