<!DOCTYPE html>
<html lang="en">
@php($brandSettings = \App\Models\CompanySettings::current())
@php($brandName = $brandSettings->company_name ?: 'Purple HR')
@php($settingsRoute = Auth::user()->isAdmin() ? url('/admin/company-settings') : route('change/password'))
@php($headerNotifications = Auth::check() ? Auth::user()->notifications()->latest()->take(8)->get() : collect())
@php($headerUnreadCount = Auth::check() ? Auth::user()->unreadNotifications()->count() : 0)
@php($isEmployeeShellRoute = Auth::check() && !Auth::user()->isAdmin() && (
    request()->is('em/dashboard')
    || request()->is('profile_user')
    || request()->is('employee/profile/*')
    || request()->is('form/leavesemployee/new')
    || request()->is('attendance/employee/page')
    || request()->is('employee/timesheets*')
    || request()->is('employee/overtime*')
    || request()->is('employee/holidays*')
    || request()->is('my/payslips*')
    || request()->is('learning/catalog*')
    || request()->is('learning/course*')
    || request()->is('performance/tracker*')
    || request()->is('performance/annual/review*')
    || request()->is('change/password*')
))
@php($isAdminShellRoute = Auth::check() && Auth::user()->isAdmin())
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

<body class="{{ $isEmployeeShellRoute ? 'employee-dashboard-shell' : '' }} {{ $isAdminShellRoute ? 'admin-dashboard-shell' : '' }}">
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
        .notification-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            margin-bottom: 4px;
        }
        .notification-tone {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: 2px 8px;
            font-size: 10px;
            line-height: 1.4;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            font-weight: 600;
            border: 1px solid transparent;
        }
        .notification-tone.info {
            color: #4f46e5;
            background: #eef2ff;
            border-color: #c7d2fe;
        }
        .notification-tone.success {
            color: #047857;
            background: #ecfdf5;
            border-color: #a7f3d0;
        }
        .notification-tone.pending {
            color: #b45309;
            background: #fffbeb;
            border-color: #fde68a;
        }
        .notification-tone.negative {
            color: #be123c;
            background: #fff1f2;
            border-color: #fecdd3;
        }
    </style>
    @includeWhen($isEmployeeShellRoute, 'employees.partials.employee-shell-style')
    @includeWhen($isEmployeeShellRoute, 'employees.partials.employee-content-style')
    @includeWhen($isAdminShellRoute, 'admin.partials.admin-shell-style')
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
                        @if($headerUnreadCount > 0)
                            <span class="badge badge-pill">{{ $headerUnreadCount > 99 ? '99+' : $headerUnreadCount }}</span>
                        @endif
                    </a>
                    <div class="dropdown-menu notifications">
                        <div class="topnav-dropdown-header">
                            <span class="notification-title">Notifications</span>
                            @if($headerNotifications->isNotEmpty())
                                <a href="javascript:void(0)" class="clear-noti js-notification-clear">Clear All</a>
                            @endif
                        </div>
                        <div class="noti-content">
                            <ul class="notification-list">
                                @forelse($headerNotifications as $item)
                                    @php($itemData = (array) $item->data)
                                    @php($itemUrl = trim((string) ($itemData['url'] ?? '')))
                                    @php($itemTitle = trim((string) ($itemData['title'] ?? 'Notification')))
                                    @php($itemMessage = trim((string) ($itemData['message'] ?? 'You have a new update.')))
                                    @php($itemTone = in_array(($itemData['tone'] ?? 'info'), ['info', 'success', 'pending', 'negative'], true) ? (string) $itemData['tone'] : 'info')
                                    <li class="notification-message {{ $item->read_at ? '' : 'bg-light' }}">
                                        <a
                                            href="{{ $itemUrl !== '' ? $itemUrl : '#' }}"
                                            class="dropdown-item js-notification-item"
                                            data-read-url="{{ route('notifications/read', ['notificationId' => $item->id]) }}"
                                        >
                                            <div class="media">
                                                <div class="media-body">
                                                    <div class="notification-meta">
                                                        <p class="noti-details mb-0"><span class="noti-title">{{ $itemTitle }}</span></p>
                                                        <span class="notification-tone {{ $itemTone }}">{{ $itemTone }}</span>
                                                    </div>
                                                    <p class="noti-time mb-0">
                                                        <span class="notification-time">{{ $itemMessage }}</span>
                                                    </p>
                                                    <small class="text-muted">{{ $item->created_at?->diffForHumans() }}</small>
                                                </div>
                                            </div>
                                        </a>
                                    </li>
                                @empty
                                    <li class="notification-message px-3 py-3 text-muted">No notifications yet.</li>
                                @endforelse
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
        @includeWhen($isEmployeeShellRoute, 'employees.partials.people-ops-modal')
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
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var csrfToken = '{{ csrf_token() }}';
            var readAllUrl = '{{ route('notifications/read-all') }}';
            var clearUrl = '{{ route('notifications/clear') }}';

            var postJson = function (url) {
                return fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({})
                });
            };

            document.addEventListener('click', function (event) {
                var clearTrigger = event.target.closest('.js-notification-clear');
                if (clearTrigger) {
                    event.preventDefault();
                    Promise.all([postJson(readAllUrl), postJson(clearUrl)]).finally(function () {
                        window.location.reload();
                    });
                    return;
                }

                var item = event.target.closest('.js-notification-item');
                if (item) {
                    var readUrl = item.getAttribute('data-read-url');
                    if (readUrl) {
                        fetch(readUrl, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({})
                        });
                    }
                }
            });
        });
    </script>
    @yield('script')
</body>
</html>
