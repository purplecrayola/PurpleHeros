<!DOCTYPE html>
<html lang="en">
@php($brandSettings = \App\Models\CompanySettings::current())
@php($brandName = $brandSettings->company_name ?: 'Purple HR')
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <meta name="description" content="{{ $brandName }} secure authentication experience.">
    <meta name="keywords" content="purple hr, login, hr software">
    <meta name="author" content="Purple Crayola">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', $brandName . ' Access')</title>
    <link rel="shortcut icon" type="image/x-icon" href="{{ $brandSettings->assetUrl('favicon_path', 'assets/img/favicon.ico') }}">
    <link rel="stylesheet" href="{{ URL::to('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ URL::to('assets/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ URL::to('assets/css/line-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ URL::to('assets/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ URL::to('assets/css/bootstrap-datetimepicker.min.css') }}">
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
            --pc-workflow-current: {{ $brandSettings->color('workflow_current_color', '#8A00FF') }};
            --pc-workflow-current-rgb: {{ $brandSettings->colorRgb('workflow_current_color', '#8A00FF') }};
            --pc-workflow-completed: {{ $brandSettings->color('workflow_completed_color', '#0F9D58') }};
            --pc-workflow-completed-rgb: {{ $brandSettings->colorRgb('workflow_completed_color', '#0F9D58') }};
            --pc-workflow-pending: {{ $brandSettings->color('workflow_pending_color', '#DCDDDF') }};
            --pc-workflow-pending-rgb: {{ $brandSettings->colorRgb('workflow_pending_color', '#DCDDDF') }};
            --pc-login-image: url('{{ $brandSettings->assetUrl('login_image_path', 'assets/img/brand/login-background.jpg') }}');
        }
    </style>
</head>
<body class="account-page error-page">
    <style>.invalid-feedback{font-size:14px;}</style>
    @yield('content')
    <script src="{{ URL::to('assets/js/jquery-3.5.1.min.js') }}"></script>
    <script src="{{ URL::to('assets/js/popper.min.js') }}"></script>
    <script src="{{ URL::to('assets/js/bootstrap.min.js') }}"></script>
    <script src="{{ URL::to('assets/js/jquery.slimscroll.min.js') }}"></script>
    <script src="{{ URL::to('assets/js/select2.min.js') }}"></script>
    <script src="{{ URL::to('assets/js/moment.min.js') }}"></script>
    <script src="{{ URL::to('assets/js/bootstrap-datetimepicker.min.js') }}"></script>
    <script src="{{ URL::to('assets/js/app.js') }}"></script>
    @yield('script')
</body>
</html>
