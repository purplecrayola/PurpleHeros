<!DOCTYPE html>
<html lang="en">
@php($brandSettings = \App\Models\CompanySettings::current())
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <meta name="description" content="Purple HR printable payroll and export views.">
    <meta name="keywords" content="purple hr, payroll export, payslip">
    <meta name="author" content="Purple Crayola">
    <meta name="robots" content="noindex, nofollow">
    <title>Purple HR Reports</title>
    <link rel="shortcut icon" type="image/x-icon" href="{{ $brandSettings->assetUrl('favicon_path', 'assets/img/favicon.ico') }}">
    <link rel="stylesheet" href="{{ URL::to('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ URL::to('assets/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ URL::to('assets/css/line-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ URL::to('assets/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ URL::to('assets/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ URL::to('assets/css/bootstrap-datetimepicker.min.css') }}">
    <link rel="stylesheet" href="{{ URL::to('assets/plugins/morris/morris.css') }}">
    <link rel="stylesheet" href="{{ URL::to('assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ URL::to('assets/css/toastr.min.css') }}">
    <script src="{{ URL::to('assets/js/toastr_jquery.min.js') }}"></script>
    <script src="{{ URL::to('assets/js/toastr.min.js') }}"></script>
    <script src="{{ URL::to('js/app.js') }}" defer></script>
</head>
<body>
<div class="main-wrapper">
    @yield('content')
</div>
</body>
</html>
