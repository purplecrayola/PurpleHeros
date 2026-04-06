@extends('layouts.master')
@section('content')
    @php($companySettings = \App\Models\CompanySettings::current())
    @php($canEditBankInfo = Auth::user()?->isAdmin() || (bool) ($companySettings->allow_employee_bank_edit ?? false))
    @php($profileUser = Auth::user()?->fresh())
    @php($displayName = trim((string) ($profileUser?->name ?: ($information?->name ?? ''))))
    @php($displayDepartment = trim((string) ($information?->department ?: ($profileUser?->department ?? ''))))
    @php($displayDesignation = trim((string) ($information?->designation ?: ($profileUser?->position ?? ''))))
    @php($displayUserId = (string) ($profileUser?->user_id ?? ''))
    @php($displayJoinDate = !empty($profileUser?->join_date) ? \Carbon\Carbon::parse($profileUser->join_date)->format('Y-m-d H:i:s') : 'N/A')
    @php($displayEmail = trim((string) ($profileUser?->email ?? ($information?->email ?? ''))))
    @php($displayPhone = trim((string) (($information?->phone_number ?? '') !== '' ? $information?->phone_number : ($profileUser?->phone_number ?? ''))))
    @php($displayBirthDate = !empty($information?->birth_date) ? \Carbon\Carbon::parse($information->birth_date)->format('j F, Y') : 'N/A')
    @php($displayAddress = trim((string) ($information?->address ?? '')) ?: 'N/A')
    @php($displayGender = trim((string) ($information?->gender ?? '')) ?: 'N/A')
    @php($displayReportsTo = trim((string) ($information?->reports_to ?? ($profileUser?->name ?? ''))) ?: 'N/A')
    @php($todayLabel = \Carbon\Carbon::now()->format('l, M j'))
    @php($brandPrimary = $companySettings->brand_primary_color ?? '#8A00FF')
    @php($brandDark = $companySettings->brand_dark_color ?? '#00163F')
    @php($brandNeutral = $companySettings->brand_neutral_color ?? '#DCDDDF')
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@700&display=swap');

        .employee-profile-modern {
            --pc-surface-primary: #fcfcfd;
            --pc-surface-secondary: #f8f8fb;
            --pc-surface-sidebar: #fafafc;
            --pc-surface-wash: #f6f1ff;
            --pc-text-primary: #171327;
            --pc-text-secondary: #5e5873;
            --pc-text-muted: #8c869e;
            --pc-text-inverse: #ffffff;
            --pc-purple-700: #5b2de1;
            --pc-purple-600: #6d28d9;
            --pc-purple-500: #7c3aed;
            --pc-purple-100: #efe7ff;
            --pc-positive: #12b981;
            --pc-pending: #f4a300;
            --pc-negative: #e35d6a;
            --pc-info: #4f46e5;
            --pc-border-subtle: #eae7f2;
            --pc-border-default: #d9d4e5;
            --pc-border-strong: #cbc4db;
            --pc-radius-sm: 8px;
            --pc-radius-md: 12px;
            --pc-radius-lg: 16px;
            --pc-shadow-soft: 0 8px 24px rgba(40, 24, 82, 0.06);
            --pc-font-display: 'Playfair Display', serif;
            --pc-font-sans: 'Inter', system-ui, -apple-system, sans-serif;
            font-size: 15px;
            font-family: var(--pc-font-sans);
            background: var(--pc-surface-primary);
            color: var(--pc-text-primary);
        }
        .employee-profile-modern .content.container-fluid {
            max-width: 1100px;
            margin: 0 auto;
            padding: 40px;
        }
        .employee-profile-modern .profile-identity-day {
            color: var(--pc-text-secondary);
            font-size: 16px;
            line-height: 24px;
            margin-bottom: 8px;
        }
        .employee-profile-modern .profile-identity-title {
            margin: 0;
            font-family: var(--pc-font-display);
            font-size: clamp(2rem, 4vw, 56px);
            line-height: 1.04;
            letter-spacing: -0.02em;
            color: var(--pc-text-primary);
        }
        .employee-profile-modern .profile-identity-subtitle {
            margin-top: 12px;
            max-width: 560px;
            color: var(--pc-text-secondary);
            font-size: 16px;
            line-height: 24px;
        }
        .employee-profile-modern .page-header {
            display: none;
        }
        .employee-profile-modern .page-header .page-title {
            font-size: 2.35rem;
            letter-spacing: -0.03em;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 6px;
        }
        .employee-profile-modern .breadcrumb {
            margin-bottom: 0;
            background: transparent;
            padding: 0;
            font-size: 0.95rem;
        }
        .employee-profile-modern .breadcrumb .breadcrumb-item a {
            color: {{ $brandPrimary }};
            font-weight: 700;
        }
        .employee-profile-modern .breadcrumb .breadcrumb-item.active {
            color: rgba(15, 23, 42, 0.68);
            font-weight: 500;
        }
        .profile-completion-card {
            border-radius: 0;
            border: 0;
            box-shadow: none;
            background: transparent;
            margin-bottom: 20px;
        }
        .profile-completion-card .card-body {
            padding: 0;
        }
        .profile-completion-card h5 {
            margin: 0;
            font-size: 1.05rem;
            font-weight: 700;
        }
        .employee-profile-modern .badge.bg-primary {
            background: var(--pc-purple-600) !important;
            color: var(--pc-text-inverse) !important;
            border-radius: 999px;
            padding: 6px 10px;
            font-weight: 700;
        }
        .employee-profile-modern .progress {
            border-radius: 999px;
            overflow: hidden;
            height: 10px;
            background: var(--pc-border-subtle);
        }
        .employee-profile-modern .progress-bar {
            background: var(--pc-purple-600) !important;
        }
        .employee-profile-modern .card.mb-0 {
            border: 0;
            box-shadow: none;
            margin-bottom: 16px !important;
            background: transparent;
        }
        .employee-profile-modern .card.mb-0 > .card-body {
            padding: 0;
        }
        .employee-profile-modern .profile-view {
            border-radius: 0;
            background: #fff;
            border: 0;
            border-bottom: 1px solid var(--pc-border-subtle);
            box-shadow: none;
            padding: 14px 0 18px;
            position: relative;
        }
        .employee-profile-modern .profile-img-wrap {
            position: static;
            width: 150px;
            height: 150px;
            border-radius: 999px;
            background: #fff;
            border: 3px solid var(--pc-purple-100);
            box-shadow: none;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 0;
        }
        .employee-profile-modern #profile_info .profile-img-wrap.edit-img {
            position: relative;
            margin: 0 auto 14px;
        }
        .employee-profile-modern .profile-avatar-upload {
            max-width: 280px;
            margin: 0 auto 16px;
            text-align: center;
        }
        .employee-profile-modern .profile-avatar-upload input[type="file"] {
            font-size: 13px;
        }
        .employee-profile-modern .profile-img-wrap .profile-img {
            width: 134px;
            height: 134px;
            border-radius: 999px;
            overflow: hidden;
        }
        .employee-profile-modern .profile-img-wrap .profile-img img,
        .employee-profile-modern #profile_info .profile-img-wrap.edit-img img.inline-block {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
            display: block;
        }
        .employee-profile-modern #profile_info .profile-img-wrap.edit-img {
            overflow: hidden;
        }
        .employee-profile-modern .profile-basic {
            margin-left: 0;
            min-height: auto;
            position: relative;
        }
        .employee-profile-modern .profile-basic::before,
        .employee-profile-modern .profile-basic::after,
        .employee-profile-modern .profile-view .profile-basic::before,
        .employee-profile-modern .profile-view .profile-basic::after {
            display: none !important;
            content: none !important;
            border: 0 !important;
        }
        .employee-profile-modern .profile-info-left .user-name {
            font-family: var(--pc-font-display);
            font-size: clamp(2rem, 3vw, 3rem);
            line-height: 1.04;
            letter-spacing: -0.02em;
            font-weight: 700;
            color: var(--pc-text-primary);
            margin-bottom: 2px;
        }
        .employee-profile-modern .profile-info-left h6 {
            color: var(--pc-text-primary) !important;
            font-size: clamp(1.35rem, 1.7vw, 1.75rem);
            font-weight: 600;
            margin-bottom: 4px;
        }
        .employee-profile-modern .profile-info-left {
            border-right: 0 !important;
            border-bottom: 0 !important;
            margin-bottom: 0 !important;
            padding-bottom: 0 !important;
        }
        .employee-profile-modern .profile-info-left small {
            color: var(--pc-text-secondary) !important;
            font-size: 16px;
            font-weight: 400;
            margin-bottom: 8px;
            display: block;
        }
        .employee-profile-modern .profile-info-left .staff-id {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            background: rgba({{ $companySettings->colorRgb('brand_primary_color', '#8A00FF') }}, 0.12);
            color: #0f172a;
            border: 1px solid rgba({{ $companySettings->colorRgb('brand_primary_color', '#8A00FF') }}, 0.24);
            font-weight: 700;
            font-size: 14px;
            padding: 6px 14px;
            margin: 1px 0 8px;
        }
        .employee-profile-modern .profile-info-left .doj {
            font-size: 16px;
            color: rgba(15, 23, 42, 0.66) !important;
            margin-bottom: 14px;
            display: inline-block;
            margin-left: 8px;
        }
        .employee-profile-modern .profile-view .staff-msg .btn-custom {
            border: 1px solid var(--pc-purple-600);
            color: #fff;
            min-height: 52px;
            border-radius: 12px;
            padding: 12px 28px;
            font-size: 16px;
            font-weight: 600;
            background: var(--pc-purple-600);
            box-shadow: none;
            transition: background-color .18s ease;
        }
        .employee-profile-modern .profile-view .staff-msg .btn-custom:hover {
            background: var(--pc-purple-700);
        }
        .employee-profile-modern .profile-view .staff-msg .btn-custom i {
            font-size: 18px;
            margin-right: 10px !important;
        }
        .employee-profile-modern .profile-basic > .row > .col-md-7 {
            border-left: 1px solid var(--pc-border-subtle) !important;
            padding-left: 40px;
            box-shadow: none !important;
            background-image: none !important;
        }
        .employee-profile-modern .profile-basic > .row > .col-md-7::before,
        .employee-profile-modern .profile-basic > .row > .col-md-7::after {
            display: none !important;
            content: none !important;
        }
        .employee-profile-modern .profile-view .personal-info,
        .employee-profile-modern .profile-box .personal-info {
            margin: 0;
        }
        .employee-profile-modern .profile-view .personal-info li,
        .employee-profile-modern .profile-box .personal-info li {
            display: grid;
            grid-template-columns: 170px minmax(0, 1fr);
            gap: 8px 16px;
            align-items: start;
            padding: 8px 0;
            border-bottom: 1px solid rgba(0, 22, 63, 0.06);
        }
        .employee-profile-modern .profile-view .personal-info li:last-child,
        .employee-profile-modern .profile-box .personal-info li:last-child {
            border-bottom: 0;
        }
        .employee-profile-modern .profile-view .personal-info li .title,
        .employee-profile-modern .profile-view .personal-info li .text,
        .employee-profile-modern .profile-box .personal-info li .title,
        .employee-profile-modern .profile-box .personal-info li .text {
            float: none !important;
            width: auto !important;
            margin: 0 !important;
            overflow: visible !important;
            min-width: 0;
        }
        .employee-profile-modern .profile-view .personal-info .title,
        .employee-profile-modern .profile-box .personal-info .title {
            font-weight: 600;
            color: var(--pc-text-secondary);
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            white-space: normal;
        }
        .employee-profile-modern .profile-view .personal-info .text,
        .employee-profile-modern .profile-box .personal-info .text {
            color: var(--pc-text-primary);
            font-weight: 500;
            font-size: 16px;
        }
        .employee-profile-modern .profile-view .personal-info .text a,
        .employee-profile-modern .profile-box .personal-info .text a {
            color: inherit;
        }
        .employee-profile-modern .profile-view .personal-info .text a:hover,
        .employee-profile-modern .profile-box .personal-info .text a:hover {
            color: {{ $brandPrimary }};
        }
        .employee-profile-modern .profile-box {
            border: 1px solid var(--pc-border-subtle);
            border-radius: var(--pc-radius-lg);
            box-shadow: none;
            background: #fff;
        }
        .employee-profile-modern .profile-box .card-body {
            padding: 18px;
        }
        .employee-profile-modern .card-title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 12px;
            font-size: 1.08rem;
            letter-spacing: -0.01em;
            font-weight: 800;
            color: #0f172a;
        }
        .employee-profile-modern .section-title {
            font-size: 0.9rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: rgba(15, 23, 42, 0.62);
            margin-bottom: 8px;
        }
        .employee-profile-modern .table {
            margin-bottom: 0;
            border-collapse: separate;
            border-spacing: 0;
        }
        .employee-profile-modern .table thead th {
            border-top: 0;
            font-size: 0.82rem;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            color: rgba(15, 23, 42, 0.6);
            background: rgba(0, 22, 63, 0.02);
        }
        .employee-profile-modern .table td {
            font-size: 0.9rem;
            color: rgba(15, 23, 42, 0.76);
            vertical-align: middle;
        }
        .profile-tab-shell {
            border: 0;
            box-shadow: none;
            border-radius: 0;
            overflow: visible;
            background: transparent;
            margin-bottom: 12px;
        }
        .profile-tab-shell .user-tabs .line-tabs {
            padding-left: 0;
            padding-right: 0;
        }
        .profile-tab-shell .nav-tabs {
            border-bottom: 1px solid var(--pc-border-subtle);
            display: flex;
            flex-wrap: wrap;
            gap: 22px;
            padding: 0;
        }
        .profile-tab-shell .nav-link {
            border: 0 !important;
            border-bottom: 2px solid transparent !important;
            border-radius: 0;
            margin: 0;
            padding: 12px 0 10px;
            font-weight: 500;
            color: var(--pc-text-secondary);
            transition: color .18s ease, border-color .18s ease;
        }
        .profile-tab-shell .nav-link.active {
            color: var(--pc-purple-600) !important;
            background: transparent;
            border-bottom-color: var(--pc-purple-600) !important;
            box-shadow: none;
        }
        .employee-profile-modern .tab-content > .tab-pane {
            animation: fadeSlide .22s ease;
        }
        @keyframes fadeSlide {
            from { opacity: 0; transform: translateY(4px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .employee-profile-modern .pro-edit {
            display: none;
        }
        .employee-profile-modern .edit-icon {
            color: var(--pc-purple-600);
            font-size: 14px;
            font-weight: 600;
            line-height: 20px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: transparent;
            border: 0;
            padding: 0;
            text-decoration: none;
            transition: color .16s ease;
        }
        .employee-profile-modern .edit-icon:hover {
            color: var(--pc-purple-700);
            text-decoration: underline;
        }
        .employee-profile-modern .experience-list .name {
            font-weight: 700;
            color: {{ $brandPrimary }};
        }
        .employee-profile-modern .experience-list .time {
            color: rgba(15, 23, 42, 0.58);
        }
        @media (max-width: 1199px) {
            .employee-profile-modern .profile-info-left .user-name {
                font-size: 1.75rem;
            }
            .employee-profile-modern .profile-view .personal-info li,
            .employee-profile-modern .profile-box .personal-info li {
                grid-template-columns: 142px minmax(0, 1fr);
            }
        }
        @media (max-width: 991px) {
            .employee-profile-modern .content.container-fluid {
                padding: 20px 14px 24px;
            }
            .employee-profile-modern .page-header .page-title {
                font-size: 1.95rem;
            }
            .employee-profile-modern .profile-basic > .row > .col-md-7 {
                border-left: 0;
                border-top: 1px dashed rgba(15, 23, 42, 0.2);
                margin-top: 16px;
                padding-top: 16px;
                padding-left: 15px;
            }
            .employee-profile-modern .profile-view .personal-info li,
            .employee-profile-modern .profile-box .personal-info li {
                grid-template-columns: 112px minmax(0, 1fr);
            }
        }
        @media (max-width: 767px) {
            body.employee-dashboard-shell .sidebar {
                left: -240px;
            }
            body.employee-dashboard-shell .page-wrapper {
                margin-left: 0;
            }
            .employee-profile-modern .page-header .page-title {
                font-size: 1.72rem;
            }
            .employee-profile-modern .profile-view,
            .employee-profile-modern .profile-box .card-body {
                padding: 15px;
            }
            .employee-profile-modern .profile-img-wrap {
                width: 102px;
                height: 102px;
                border-radius: 22px;
            }
            .employee-profile-modern .profile-img-wrap .profile-img {
                width: 90px;
                height: 90px;
                border-radius: 18px;
            }
            .employee-profile-modern .profile-info-left .user-name {
                font-size: 1.5rem;
            }
            .employee-profile-modern .profile-view .personal-info li,
            .employee-profile-modern .profile-box .personal-info li {
                grid-template-columns: 1fr;
                gap: 2px;
                padding: 10px 0;
            }
        }
    </style>
    <div class="page-wrapper employee-profile-modern">
        <!-- Page Content -->
        <div class="content container-fluid">
            @include('employees.partials.employee-topbar', ['context' => 'Self-service profile workspace'])
            <div class="mb-4">
                <p class="profile-identity-day">{{ $todayLabel }}</p>
                <h1 class="profile-identity-title">My Profile</h1>
                <p class="profile-identity-subtitle">Manage your personal records and statutory information from a single self-service workspace.</p>
            </div>
            <!-- Page Header -->
            <div class="page-header">
                <div class="row">
                    <div class="col-sm-12">
                        <h3 class="page-title">Profile</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ Auth::user()?->isAdmin() ? route('home') : route('em/dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Profile</li>
                        </ul>
                    </div>
                </div>
            </div>
            {{-- message --}}
            {!! Toastr::message() !!}
            <div class="card mb-3 profile-completion-card">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="mb-0">Profile Completion</h5>
                        <span class="badge bg-primary">{{ (int) ($profileCompletion ?? 0) }}%</span>
                    </div>
                    <div class="progress" style="height: 10px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ (int) ($profileCompletion ?? 0) }}%;" aria-valuenow="{{ (int) ($profileCompletion ?? 0) }}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <small class="text-muted">Complete personal, statutory, onboarding, and reference sections to reach 100%.</small>
                </div>
            </div>
            <!-- /Page Header -->
            <div class="card tab-box profile-tab-shell">
                <div class="row user-tabs">
                    <div class="col-lg-12 col-md-12 col-sm-12 line-tabs">
                        <ul class="nav nav-tabs nav-tabs-bottom">
                            <li class="nav-item"><a href="#emp_profile" data-toggle="tab" class="nav-link active">Profile</a></li>
                            <li class="nav-item"><a href="#emp_onboarding" data-toggle="tab" class="nav-link">Onboarding</a></li>
                            <li class="nav-item"><a href="#emp_projects" data-toggle="tab" class="nav-link">Projects</a></li>
                            @if(Auth::user()?->isAdmin())
                                <li class="nav-item"><a href="#bank_statutory" data-toggle="tab" class="nav-link">Bank & Statutory <small class="text-danger">(Admin Only)</small></a></li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="tab-content">
                <div id="emp_profile" class="pro-overview tab-pane fade show active">
            <div class="card mb-0">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="profile-view">
                                <div class="profile-img-wrap">
                                    <div class="profile-img">
                                        <a href="#">
                                            <img class="user-profile" alt="" src="{{ $profileUser?->avatar_url ?? Auth::user()->avatar_url }}" alt="{{ $displayName }}">
                                        </a>
                                    </div>
                                </div>
                                <div class="profile-basic">
                                    <div class="row">
                                        <div class="col-md-5">
                                            <div class="profile-info-left">
                                                <h3 class="user-name m-t-0 mb-0">{{ $displayName }}</h3>
                                                <h6 class="text-muted">{{ $displayDepartment !== '' ? $displayDepartment : 'N/A' }}</h6>
                                                <small class="text-muted">{{ $displayDesignation !== '' ? $displayDesignation : 'N/A' }}</small>
                                                <div class="staff-id">User ID : {{ $displayUserId !== '' ? $displayUserId : 'N/A' }}</div>
                                                <div class="small doj text-muted">Date of Join : {{ $displayJoinDate }}</div>
                                                <div class="staff-msg"><a class="btn btn-custom" href="mailto:{{ $displayEmail }}"><i class="la la-paper-plane-o mr-2"></i>Send Message</a></div>
                                            </div>
                                        </div>
                                        <div class="col-md-7">
                                            <ul class="personal-info">
                                                <li>
                                                    <div class="title">Phone:</div>
                                                    <div class="text"><a href="#">{{ $displayPhone !== '' ? $displayPhone : 'N/A' }}</a></div>
                                                </li>
                                                <li>
                                                    <div class="title">Email:</div>
                                                    <div class="text"><a href="#">{{ $displayEmail !== '' ? $displayEmail : 'N/A' }}</a></div>
                                                </li>
                                                <li>
                                                    <div class="title">Birthday:</div>
                                                    <div class="text">{{ $displayBirthDate }}</div>
                                                </li>
                                                <li>
                                                    <div class="title">Address:</div>
                                                    <div class="text">{{ $displayAddress }}</div>
                                                </li>
                                                <li>
                                                    <div class="title">Gender:</div>
                                                    <div class="text">{{ $displayGender }}</div>
                                                </li>
                                                <li>
                                                    <div class="title">Reports to:</div>
                                                    <div class="text">
                                                        <div class="avatar-box">
                                                            <div class="avatar avatar-xs">
                                                                <img src="{{ $profileUser?->avatar_url ?? Auth::user()->avatar_url }}" alt="{{ $displayName }}">
                                                            </div>
                                                        </div>
                                                        <a href="#">
                                                            {{ $displayReportsTo }}
                                                        </a>
                                                    </div>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div class="pro-edit"><a data-target="#profile_info" data-toggle="modal" class="edit-icon" href="#">Edit</a></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
					
                    <div class="row">
                        <!-- Personal Informations -->
                        <div class="col-md-6 d-flex">
                            <div class="card profile-box flex-fill">
                                <div class="card-body">
                                    <h3 class="card-title">Personal Information <a href="#" class="edit-icon" data-toggle="modal" data-target="#personal_info_modal">Edit</a></h3>
                                    <ul class="personal-info">
                                        <li>
                                            <div class="title">Passport No.</div>
                                            @if (!empty($userInformation->passport_no))
                                                <div class="text">{{ $userInformation->passport_no }}</div>
                                            @else
                                                <div class="text">N/A</div>
                                            @endif
                                        </li>
                                        <li>
                                            <div class="title">Passport Exp Date.</div>
                                            @if (!empty($userInformation->passport_expiry_date))
                                                <div class="text">{{ $userInformation->passport_expiry_date }}</div>
                                            @else
                                                <div class="text">N/A</div>
                                            @endif
                                        </li>
                                        <li>
                                            <div class="title">Tel</div>
                                            @if (!empty($userInformation->tel))
                                                <div class="text">{{ $userInformation->tel }}</div>
                                            @else
                                                <div class="text">N/A</div>
                                            @endif
                                        </li>
                                        <li>
                                            <div class="title">Nationality</div>
                                            @if (!empty($userInformation->nationality))
                                                <div class="text">{{ $userInformation->nationality }}</div>
                                            @else
                                                <div class="text">N/A</div>
                                            @endif
                                        </li>
                                        <li>
                                            <div class="title">Religion</div>
                                            @if (!empty($userInformation->religion))
                                                <div class="text">{{ $userInformation->religion }}</div>
                                            @else
                                                <div class="text">N/A</div>
                                            @endif
                                        </li>
                                        <li>
                                            <div class="title">Marital status</div>
                                            @if (!empty($userInformation->marital_status))
                                                <div class="text">{{ $userInformation->marital_status }}</div>
                                            @else
                                                <div class="text">N/A</div>
                                            @endif
                                        </li>
                                        <li>
                                            <div class="title">Employment of spouse</div>
                                            @if (!empty($userInformation->employment_of_spouse))
                                                <div class="text">{{ $userInformation->employment_of_spouse }}</div>
                                            @else
                                                <div class="text">N/A</div>
                                            @endif
                                        </li>
                                        <li>
                                            <div class="title">No. of children</div>
                                            @if (!empty($userInformation->children))
                                                <div class="text">{{ $userInformation->children }}</div>
                                            @else
                                                <div class="text">N/A</div>
                                            @endif
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <!-- End Personal Informations -->
                        
                        <!-- Emergency Contact -->
                        <div class="col-md-6 d-flex">
                            <div class="card profile-box flex-fill">
                                <div class="card-body">
                                    <h3 class="card-title">Emergency Contact
                                        <a href="#" class="edit-icon" data-toggle="modal" data-target="#emergency_contact_modal">
                                            Edit
                                        </a>
                                    </h3>
                                    <h5 class="section-title">Primary</h5>
                                    <ul class="personal-info">
                                        <li>
                                            <div class="title">Name</div>
                                            @if (!empty($emergencyContact->name_primary))
                                            <div class="text">{{ $emergencyContact->name_primary }}</div>
                                            @else
                                            <div class="text">N/A</div>
                                            @endif
                                        </li>
                                        <li>
                                            <div class="title">Relationship</div>
                                            @if (!empty($emergencyContact->relationship_primary))
                                            <div class="text">{{ $emergencyContact->relationship_primary }}</div>
                                            @else
                                            <div class="text">N/A</div>
                                            @endif
                                        </li>
                                        <li>
                                            <div class="title">Phone </div>
                                            @if (!empty($emergencyContact->phone_primary) && !empty($emergencyContact->phone_2_primary))
                                            <div class="text">{{ $emergencyContact->phone_primary }},{{ $emergencyContact->phone_2_primary }}</div>
                                            @else
                                            <div class="text">N/A</div>
                                            @endif
                                        </li>
                                    </ul>
                                    <hr>
                                    <h5 class="section-title">Secondary</h5>
                                    <ul class="personal-info">
                                        <li>
                                            <div class="title">Name</div>
                                            @if (!empty($emergencyContact->name_secondary))
                                            <div class="text">{{ $emergencyContact->name_secondary }}</div>
                                            @else
                                            <div class="text">N/A</div>
                                            @endif
                                        </li>
                                        <li>
                                            <div class="title">Relationship</div>
                                            @if (!empty($emergencyContact->relationship_secondary))
                                            <div class="text">{{ $emergencyContact->relationship_secondary }}</div>
                                            @else
                                            <div class="text">N/A</div>
                                            @endif
                                        </li>
                                        <li>
                                            <div class="title">Phone </div>
                                            @if (!empty($emergencyContact->phone_secondary) && !empty($emergencyContact->phone_2_secondary))
                                            <div class="text">{{ $emergencyContact->phone_secondary }},{{ $emergencyContact->phone_2_secondary }}</div>
                                            @else
                                            <div class="text">N/A</div>
                                            @endif
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <!-- End Emergency Contact -->
                    </div>

                    <div class="row">
                        <!-- Bank information -->
                        <div class="col-md-6 d-flex">
                            <div class="card profile-box flex-fill">
                                <div class="card-body">
                                    <h3 class="card-title">Bank information 
                                        @if($canEditBankInfo)
                                            <a href="#" class="edit-icon" data-toggle="modal" data-target="#bank_information_modal">
                                                Edit
                                            </a>
                                        @endif
                                    </h3>
                                    @if(! $canEditBankInfo)
                                        <p class="text-muted mb-2">Read-only. Bank information is managed by administrators.</p>
                                    @endif
                                    <ul class="personal-info">
                                        <li>
                                            <div class="title font-weight-bold">Primary Account</div>
                                            <div class="text"> </div>
                                        </li>
                                        <li>
                                            <div class="title">Bank name</div>
                                            @if(!empty($bankInformation?->primary_bank_name) || !empty($bankInformation?->bank_name))
                                                <div class="text">{{ $bankInformation?->primary_bank_name ?: $bankInformation?->bank_name }}</div>
                                            @else  
                                                <div class="text">N/A</div>
                                            @endif
                                        </li>
                                        <li>
                                            <div class="title">Bank account No.</div>
                                            @if(!empty($bankInformation?->primary_bank_account_no) || !empty($bankInformation?->bank_account_no))
                                                <div class="text">{{ $bankInformation?->primary_bank_account_no ?: $bankInformation?->bank_account_no }}</div>
                                            @else  
                                                <div class="text">N/A</div>
                                            @endif
                                        </li>
                                        <li>
                                            <div class="title">Bank Code (NIP)</div>
                                            @if(!empty($bankInformation?->primary_ifsc_code) || !empty($bankInformation?->ifsc_code))
                                                <div class="text">{{ $bankInformation?->primary_ifsc_code ?: $bankInformation?->ifsc_code }}</div>
                                            @else  
                                                <div class="text">N/A</div>
                                            @endif
                                        </li>
                                        <li>
                                            <div class="title">PAN No</div>
                                            @if(!empty($bankInformation?->primary_pan_no) || !empty($bankInformation?->pan_no))
                                                <div class="text">{{ $bankInformation?->primary_pan_no ?: $bankInformation?->pan_no }}</div>
                                            @else  
                                                <div class="text">N/A</div>
                                            @endif
                                        </li>
                                        <li>
                                            <div class="title font-weight-bold">Secondary Account</div>
                                            <div class="text"> </div>
                                        </li>
                                        <li>
                                            <div class="title">Bank name</div>
                                            <div class="text">{{ $bankInformation?->secondary_bank_name ?: 'N/A' }}</div>
                                        </li>
                                        <li>
                                            <div class="title">Bank account No.</div>
                                            <div class="text">{{ $bankInformation?->secondary_bank_account_no ?: 'N/A' }}</div>
                                        </li>
                                        <li>
                                            <div class="title">Bank Code (NIP)</div>
                                            <div class="text">{{ $bankInformation?->secondary_ifsc_code ?: 'N/A' }}</div>
                                        </li>
                                        <li>
                                            <div class="title">PAN No</div>
                                            <div class="text">{{ $bankInformation?->secondary_pan_no ?: 'N/A' }}</div>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <!-- /Bank information -->

                        <div class="col-md-6 d-flex">
                            <div class="card profile-box flex-fill">
                                <div class="card-body">
                                    <h3 class="card-title">Family Information <a href="#" class="edit-icon" data-toggle="modal" data-target="#family_info_modal">Edit</a></h3>
                                    <div class="table-responsive">
                                        <table class="table table-nowrap">
                                            <thead>
                                                <tr>
                                                    <th>Name</th>
                                                    <th>Relationship</th>
                                                    <th>Date of Birth</th>
                                                    <th>Phone</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse(($familyMembers ?? collect()) as $member)
                                                    <tr>
                                                        <td>{{ $member->name ?: 'N/A' }}</td>
                                                        <td>
                                                            {{ $member->relationship ?: 'N/A' }}
                                                            @if($member->is_next_of_kin)
                                                                <small class="text-success">(Next of kin)</small>
                                                            @endif
                                                        </td>
                                                        <td>{{ $member->date_of_birth ? \Carbon\Carbon::parse($member->date_of_birth)->format('M j, Y') : 'N/A' }}</td>
                                                        <td>{{ $member->phone ?: 'N/A' }}</td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="4" class="text-muted">No family information recorded.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="row">
                        <div class="col-md-6 d-flex">
                            <div class="card profile-box flex-fill">
                                <div class="card-body">
                                    <h3 class="card-title">Education Information <a href="#emp_onboarding" class="edit-icon" data-toggle="tab">Edit</a></h3>
                                    <div class="experience-box">
                                        <ul class="experience-list">
                                            @forelse(($educations ?? collect()) as $education)
                                                <li>
                                                    <div class="experience-user">
                                                        <div class="before-circle"></div>
                                                    </div>
                                                    <div class="experience-content">
                                                        <div class="timeline-content">
                                                            <a href="#/" class="name">{{ $education->institution ?: 'Institution' }}</a>
                                                            <div>{{ trim(($education->degree ?: '') . ' ' . ($education->field_of_study ? '(' . $education->field_of_study . ')' : '')) ?: 'N/A' }}</div>
                                                            <span class="time">
                                                                {{ $education->start_date ? \Carbon\Carbon::parse($education->start_date)->format('M Y') : 'N/A' }}
                                                                -
                                                                {{ $education->end_date ? \Carbon\Carbon::parse($education->end_date)->format('M Y') : 'Present' }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </li>
                                            @empty
                                                <li>
                                                    <div class="experience-content">
                                                        <div class="timeline-content text-muted">No education records yet.</div>
                                                    </div>
                                                </li>
                                            @endforelse
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 d-flex">
                            <div class="card profile-box flex-fill">
                                <div class="card-body">
                                    <h3 class="card-title">Experience <a href="#emp_onboarding" class="edit-icon" data-toggle="tab">Edit</a></h3>
                                    <div class="experience-box">
                                        <ul class="experience-list">
                                            @forelse(($experiences ?? collect()) as $experience)
                                                <li>
                                                    <div class="experience-user">
                                                        <div class="before-circle"></div>
                                                    </div>
                                                    <div class="experience-content">
                                                        <div class="timeline-content">
                                                            <a href="#/" class="name">{{ ($experience->job_title ?: 'Role') . ' at ' . ($experience->company_name ?: 'Company') }}</a>
                                                            <span class="time">
                                                                {{ $experience->start_date ? \Carbon\Carbon::parse($experience->start_date)->format('M Y') : 'N/A' }}
                                                                -
                                                                {{ $experience->is_current ? 'Present' : ($experience->end_date ? \Carbon\Carbon::parse($experience->end_date)->format('M Y') : 'N/A') }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </li>
                                            @empty
                                                <li>
                                                    <div class="experience-content">
                                                        <div class="timeline-content text-muted">No work experience records yet.</div>
                                                    </div>
                                                </li>
                                            @endforelse
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /Profile Info Tab -->

                <div class="tab-pane fade" id="emp_onboarding">
                    <div class="card">
                        <div class="card-body">
                            <h3 class="card-title">Onboarding Information</h3>
                            <p class="text-muted">Add education, previous experience, CV/certifications, and references. Admin can review this from the employee admin profile.</p>
                            <form action="{{ route('profile/onboarding/save') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="user_id" value="{{ $displayUserId }}">

                                <h5 class="mb-3">Documents</h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Upload / Replace CV</label>
                                            <input type="file" class="form-control" name="cv_file" accept=".pdf,.doc,.docx">
                                        </div>
                                        @php($cvDoc = collect($documents ?? [])->firstWhere('document_type', 'cv'))
                                        @if($cvDoc)
                                            <small class="text-muted d-block">Current CV:
                                                <a href="{{ $cvDoc->file_url }}" target="_blank">{{ $cvDoc->title ?: 'Download' }}</a>
                                            </small>
                                        @endif
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Upload Certifications (multiple)</label>
                                            <input type="file" class="form-control" name="certification_files[]" accept=".pdf,.jpg,.jpeg,.png" multiple>
                                        </div>
                                        <small class="text-muted d-block">Accepted: PDF, JPG, PNG.</small>
                                    </div>
                                </div>

                                <hr>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h5 class="mb-0">Education</h5>
                                    <button type="button" class="btn btn-outline-primary btn-sm" id="add-education-row">Add Education</button>
                                </div>
                                <div id="education-rows">
                                    @forelse(($educations ?? collect()) as $index => $education)
                                        <div class="row border rounded p-2 mb-2 education-row">
                                            <div class="col-md-4"><input class="form-control" name="educations[{{ $index }}][institution]" placeholder="Institution" value="{{ $education->institution }}"></div>
                                            <div class="col-md-4"><input class="form-control" name="educations[{{ $index }}][degree]" placeholder="Degree" value="{{ $education->degree }}"></div>
                                            <div class="col-md-4"><input class="form-control" name="educations[{{ $index }}][field_of_study]" placeholder="Field of Study" value="{{ $education->field_of_study }}"></div>
                                            <div class="col-md-3 mt-2"><input type="date" class="form-control" name="educations[{{ $index }}][start_date]" value="{{ optional($education->start_date)->format('Y-m-d') }}"></div>
                                            <div class="col-md-3 mt-2"><input type="date" class="form-control" name="educations[{{ $index }}][end_date]" value="{{ optional($education->end_date)->format('Y-m-d') }}"></div>
                                            <div class="col-md-4 mt-2"><input class="form-control" name="educations[{{ $index }}][grade]" placeholder="Grade" value="{{ $education->grade }}"></div>
                                            <div class="col-md-2 mt-2"><button type="button" class="btn btn-outline-danger btn-sm remove-row">Remove</button></div>
                                        </div>
                                    @empty
                                        <div class="row border rounded p-2 mb-2 education-row">
                                            <div class="col-md-4"><input class="form-control" name="educations[0][institution]" placeholder="Institution"></div>
                                            <div class="col-md-4"><input class="form-control" name="educations[0][degree]" placeholder="Degree"></div>
                                            <div class="col-md-4"><input class="form-control" name="educations[0][field_of_study]" placeholder="Field of Study"></div>
                                            <div class="col-md-3 mt-2"><input type="date" class="form-control" name="educations[0][start_date]"></div>
                                            <div class="col-md-3 mt-2"><input type="date" class="form-control" name="educations[0][end_date]"></div>
                                            <div class="col-md-4 mt-2"><input class="form-control" name="educations[0][grade]" placeholder="Grade"></div>
                                            <div class="col-md-2 mt-2"><button type="button" class="btn btn-outline-danger btn-sm remove-row">Remove</button></div>
                                        </div>
                                    @endforelse
                                </div>

                                <hr>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h5 class="mb-0">Previous Work Experience</h5>
                                    <button type="button" class="btn btn-outline-primary btn-sm" id="add-experience-row">Add Experience</button>
                                </div>
                                <div id="experience-rows">
                                    @forelse(($experiences ?? collect()) as $index => $experience)
                                        <div class="row border rounded p-2 mb-2 experience-row">
                                            <div class="col-md-4"><input class="form-control" name="experiences[{{ $index }}][company_name]" placeholder="Company" value="{{ $experience->company_name }}"></div>
                                            <div class="col-md-4"><input class="form-control" name="experiences[{{ $index }}][job_title]" placeholder="Job Title" value="{{ $experience->job_title }}"></div>
                                            <div class="col-md-4"><input class="form-control" name="experiences[{{ $index }}][location]" placeholder="Location" value="{{ $experience->location }}"></div>
                                            <div class="col-md-3 mt-2"><input type="date" class="form-control" name="experiences[{{ $index }}][start_date]" value="{{ optional($experience->start_date)->format('Y-m-d') }}"></div>
                                            <div class="col-md-3 mt-2"><input type="date" class="form-control" name="experiences[{{ $index }}][end_date]" value="{{ optional($experience->end_date)->format('Y-m-d') }}"></div>
                                            <div class="col-md-2 mt-2 d-flex align-items-center"><label class="mb-0"><input type="checkbox" name="experiences[{{ $index }}][is_current]" value="1" {{ $experience->is_current ? 'checked' : '' }}> Current</label></div>
                                            <div class="col-md-4 mt-2"><input class="form-control" name="experiences[{{ $index }}][summary]" placeholder="Summary" value="{{ $experience->summary }}"></div>
                                            <div class="col-md-2 mt-2"><button type="button" class="btn btn-outline-danger btn-sm remove-row">Remove</button></div>
                                        </div>
                                    @empty
                                        <div class="row border rounded p-2 mb-2 experience-row">
                                            <div class="col-md-4"><input class="form-control" name="experiences[0][company_name]" placeholder="Company"></div>
                                            <div class="col-md-4"><input class="form-control" name="experiences[0][job_title]" placeholder="Job Title"></div>
                                            <div class="col-md-4"><input class="form-control" name="experiences[0][location]" placeholder="Location"></div>
                                            <div class="col-md-3 mt-2"><input type="date" class="form-control" name="experiences[0][start_date]"></div>
                                            <div class="col-md-3 mt-2"><input type="date" class="form-control" name="experiences[0][end_date]"></div>
                                            <div class="col-md-2 mt-2 d-flex align-items-center"><label class="mb-0"><input type="checkbox" name="experiences[0][is_current]" value="1"> Current</label></div>
                                            <div class="col-md-4 mt-2"><input class="form-control" name="experiences[0][summary]" placeholder="Summary"></div>
                                            <div class="col-md-2 mt-2"><button type="button" class="btn btn-outline-danger btn-sm remove-row">Remove</button></div>
                                        </div>
                                    @endforelse
                                </div>

                                <hr>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h5 class="mb-0">References</h5>
                                    <button type="button" class="btn btn-outline-primary btn-sm" id="add-reference-row">Add Reference</button>
                                </div>
                                <div id="reference-rows">
                                    @forelse(($references ?? collect()) as $index => $reference)
                                        <div class="row border rounded p-2 mb-2 reference-row">
                                            <div class="col-md-4"><input class="form-control" name="references[{{ $index }}][referee_name]" placeholder="Referee Name" value="{{ $reference->referee_name }}"></div>
                                            <div class="col-md-4"><input class="form-control" name="references[{{ $index }}][relationship]" placeholder="Relationship" value="{{ $reference->relationship }}"></div>
                                            <div class="col-md-4"><input class="form-control" name="references[{{ $index }}][company_name]" placeholder="Company" value="{{ $reference->company_name }}"></div>
                                            <div class="col-md-3 mt-2"><input class="form-control" name="references[{{ $index }}][job_title]" placeholder="Job Title" value="{{ $reference->job_title }}"></div>
                                            <div class="col-md-3 mt-2"><input class="form-control" name="references[{{ $index }}][email]" placeholder="Email" value="{{ $reference->email }}"></div>
                                            <div class="col-md-3 mt-2"><input class="form-control" name="references[{{ $index }}][phone]" placeholder="Phone" value="{{ $reference->phone }}"></div>
                                            <div class="col-md-3 mt-2"><input class="form-control" name="references[{{ $index }}][years_known]" placeholder="Years Known" value="{{ $reference->years_known }}"></div>
                                            @if(Auth::user()?->isAdmin())
                                                <div class="col-md-2 mt-2 d-flex align-items-center"><label class="mb-0"><input type="checkbox" name="references[{{ $index }}][is_verified]" value="1" {{ $reference->is_verified ? 'checked' : '' }}> Verified</label></div>
                                                <div class="col-md-8 mt-2"><input class="form-control" name="references[{{ $index }}][verification_feedback]" placeholder="Verification Feedback" value="{{ $reference->verification_feedback }}"></div>
                                            @else
                                                <div class="col-md-2 mt-2 d-flex align-items-center">
                                                    <span class="badge {{ $reference->is_verified ? 'bg-success' : 'bg-secondary' }}">{{ $reference->is_verified ? 'Verified' : 'Pending' }}</span>
                                                </div>
                                                <div class="col-md-8 mt-2"><input class="form-control" value="{{ $reference->verification_feedback }}" placeholder="Admin feedback" readonly></div>
                                            @endif
                                            <div class="col-md-2 mt-2"><button type="button" class="btn btn-outline-danger btn-sm remove-row">Remove</button></div>
                                        </div>
                                    @empty
                                        <div class="row border rounded p-2 mb-2 reference-row">
                                            <div class="col-md-4"><input class="form-control" name="references[0][referee_name]" placeholder="Referee Name"></div>
                                            <div class="col-md-4"><input class="form-control" name="references[0][relationship]" placeholder="Relationship"></div>
                                            <div class="col-md-4"><input class="form-control" name="references[0][company_name]" placeholder="Company"></div>
                                            <div class="col-md-3 mt-2"><input class="form-control" name="references[0][job_title]" placeholder="Job Title"></div>
                                            <div class="col-md-3 mt-2"><input class="form-control" name="references[0][email]" placeholder="Email"></div>
                                            <div class="col-md-3 mt-2"><input class="form-control" name="references[0][phone]" placeholder="Phone"></div>
                                            <div class="col-md-3 mt-2"><input class="form-control" name="references[0][years_known]" placeholder="Years Known"></div>
                                            <div class="col-md-2 mt-2"><button type="button" class="btn btn-outline-danger btn-sm remove-row">Remove</button></div>
                                        </div>
                                    @endforelse
                                </div>

                                <div class="submit-section mt-3">
                                    <button type="submit" class="btn btn-primary submit-btn">Save Onboarding Information</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                    
                <!-- Projects Tab -->
                <div class="tab-pane fade" id="emp_projects">
                    <div class="row">
                        @forelse(($projectSnapshots ?? collect()) as $project)
                            <div class="col-lg-4 col-sm-6 col-md-6">
                                <div class="card">
                                    <div class="card-body">
                                        <h4 class="project-title">{{ $project->project_name ?: 'Unspecified Workstream' }}</h4>
                                        <small class="block text-ellipsis m-b-15">
                                            <span class="text-xs">{{ $project->entry_count }}</span>
                                            <span class="text-muted">timesheet entries</span>
                                        </small>
                                        <p class="text-muted mb-2">
                                            Worked {{ $project->worked_hours }}h of {{ $project->assigned_hours }}h assigned.
                                        </p>
                                        <div class="pro-deadline m-b-15">
                                            <div class="sub-title">Last activity:</div>
                                            <div class="text-muted">
                                                {{ $project->last_activity ? \Carbon\Carbon::parse($project->last_activity)->format('M j, Y') : 'N/A' }}
                                            </div>
                                        </div>
                                        <p class="m-b-5">Progress <span class="text-success float-right">{{ $project->progress }}%</span></p>
                                        <div class="progress progress-xs mb-0">
                                            <div style="width: {{ $project->progress }}%" role="progressbar" class="progress-bar bg-success"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="mb-2">No Projects Yet</h5>
                                        <p class="text-muted mb-0">Projects are generated from your timesheet workstreams. Once you log time, they appear here automatically.</p>
                                    </div>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>
                <!-- /Projects Tab -->
                
                <!-- Bank Statutory Tab -->
                <div class="tab-pane fade" id="bank_statutory">
                    <div class="card">
                        <div class="card-body">
                            <h3 class="card-title">Bank & Statutory Setup</h3>
                            <form action="{{ route('profile/bank-statutory/save') }}" method="POST">
                                @csrf
                                <input type="hidden" name="user_id" value="{{ Auth::user()->user_id }}">

                                <h4 class="card-title mb-3">Payroll Defaults</h4>
                                <div class="row">
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label class="col-form-label">Salary amount <small class="text-muted">per month</small></label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">₦</span>
                                                </div>
                                                <input type="number" min="0" step="0.01" class="form-control" name="salary_amount" value="{{ old('salary_amount', $salaryRecord->salary ?? '') }}" placeholder="Enter monthly gross salary">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label class="col-form-label">Tax station</label>
                                            <input type="text" class="form-control" name="tax_station" value="{{ old('tax_station', $statutoryProfile->tax_station ?? '') }}" placeholder="Lagos Mainland">
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label class="col-form-label">Tax residency state</label>
                                            <input type="text" class="form-control" name="tax_residency_state" value="{{ old('tax_residency_state', $statutoryProfile->tax_residency_state ?? '') }}" placeholder="Lagos">
                                        </div>
                                    </div>
                                </div>

                                <hr>
                                <h4 class="card-title mb-3">Nigeria Statutory Values</h4>
                                <div class="row">
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label class="col-form-label">Annual rent for relief (NGN)</label>
                                            <input type="number" min="0" step="0.01" class="form-control" name="annual_rent" value="{{ old('annual_rent', $statutoryProfile->annual_rent ?? 0) }}">
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label class="col-form-label">Other statutory deductions (monthly NGN)</label>
                                            <input type="number" min="0" step="0.01" class="form-control" name="other_statutory_deductions" value="{{ old('other_statutory_deductions', $statutoryProfile->other_statutory_deductions ?? 0) }}">
                                        </div>
                                    </div>
                                    <div class="col-sm-4">
                                        <div class="form-group">
                                            <label class="col-form-label">Default non-taxable reimbursement (NGN)</label>
                                            <input type="number" min="0" step="0.01" class="form-control" name="default_non_taxable_reimbursement" value="{{ old('default_non_taxable_reimbursement', $statutoryProfile->default_non_taxable_reimbursement ?? 0) }}">
                                        </div>
                                    </div>
                                </div>

                                <h4 class="card-title mb-3 mt-4">Pension (Nigeria)</h4>
                                <div class="row">
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label class="col-form-label">Enable pension deduction</label>
                                            <select class="select" name="pension_enabled">
                                                <option value="1" {{ (int) old('pension_enabled', (int) ($statutoryProfile->pension_enabled ?? 1)) === 1 ? 'selected' : '' }}>Yes</option>
                                                <option value="0" {{ (int) old('pension_enabled', (int) ($statutoryProfile->pension_enabled ?? 1)) === 0 ? 'selected' : '' }}>No</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label class="col-form-label">Employee pension rate (%)</label>
                                            <input type="number" min="0" max="100" step="0.01" class="form-control" name="employee_pension_rate_percent" value="{{ old('employee_pension_rate_percent', $statutoryProfile->employee_pension_rate_percent ?? 8) }}">
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label class="col-form-label">Employer pension rate (%)</label>
                                            <input type="number" min="0" max="100" step="0.01" class="form-control" name="employer_pension_rate_percent" value="{{ old('employer_pension_rate_percent', $statutoryProfile->employer_pension_rate_percent ?? 10) }}">
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label class="col-form-label">Pension PIN</label>
                                            <input type="text" class="form-control" name="pension_pin" value="{{ old('pension_pin', $statutoryProfile->pension_pin ?? '') }}" placeholder="Optional">
                                        </div>
                                    </div>
                                </div>

                                <h4 class="card-title mb-3 mt-4">NHF (Nigeria)</h4>
                                <div class="row">
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label class="col-form-label">Enable NHF deduction</label>
                                            <select class="select" name="nhf_enabled">
                                                <option value="1" {{ (int) old('nhf_enabled', (int) ($statutoryProfile->nhf_enabled ?? 0)) === 1 ? 'selected' : '' }}>Yes</option>
                                                <option value="0" {{ (int) old('nhf_enabled', (int) ($statutoryProfile->nhf_enabled ?? 0)) === 0 ? 'selected' : '' }}>No</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label class="col-form-label">NHF rate (%)</label>
                                            <input type="number" min="0" max="100" step="0.01" class="form-control" name="nhf_rate_percent" value="{{ old('nhf_rate_percent', $statutoryProfile->nhf_rate_percent ?? 2.5) }}">
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label class="col-form-label">NHF base cap (NGN)</label>
                                            <input type="number" min="0" step="0.01" class="form-control" name="nhf_base_cap" value="{{ old('nhf_base_cap', $statutoryProfile->nhf_base_cap ?? '') }}" placeholder="Optional">
                                        </div>
                                    </div>
                                    <div class="col-sm-3">
                                        <div class="form-group">
                                            <label class="col-form-label">NHF number</label>
                                            <input type="text" class="form-control" name="nhf_number" value="{{ old('nhf_number', $statutoryProfile->nhf_number ?? '') }}" placeholder="Optional">
                                        </div>
                                    </div>
                                </div>

                                <div class="submit-section">
                                    <button class="btn btn-primary submit-btn" type="submit">Save</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <!-- /Bank Statutory Tab -->
            </div>
        </div>
        <!-- /Page Content -->
        @if(!empty($information))
        <!-- Profile Modal -->
        <div id="profile_info" class="modal custom-modal fade" role="dialog">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Profile Information</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('profile/information/save') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="profile-img-wrap edit-img">
                                        <img class="inline-block" src="{{ Auth::user()->avatar_url }}" alt="{{ Auth::user()->name }}">
                                    </div>
                                    <div class="profile-avatar-upload">
                                        <label class="mb-1 d-block">Profile Picture</label>
                                        <input class="form-control" type="file" name="images" accept="image/*">
                                        <small class="text-muted d-block mt-1">JPG, PNG, or WEBP. Max 5MB.</small>
                                        <input type="hidden" name="hidden_image" id="e_image" value="{{ Auth::user()->avatar }}">
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label>Full Name</label>
                                                <input type="text" class="form-control" id="name" name="name" value="{{ Auth::user()->name }}">
                                                <input type="hidden" class="form-control" id="user_id" name="user_id" value="{{ Auth::user()->user_id }}">
                                                <input type="hidden" class="form-control" id="email" name="email" value="{{ Auth::user()->email }}">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Birth Date</label>
                                                <div class="cal-icon">
                                                    <input class="form-control datetimepicker" type="text" id="birthDate" name="birthDate" value="{{ $information->birth_date }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Gender</label>
                                                <select class="select form-control" id="gender" name="gender">
                                                    <option value="{{ $information->gender }}" {{ ( $information->gender == $information->gender) ? 'selected' : '' }}>{{ $information->gender }} </option>
                                                    <option value="Male">Male</option>
                                                    <option value="Female">Female</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Address</label>
                                        <input type="text" class="form-control" id="address" name="address" value="{{ $information->address }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>State</label>
                                        <input type="text" class="form-control" id="state" name="state" value="{{ $information->state }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Country</label>
                                        <input type="text" class="form-control" id="" name="country" value="{{ $information->country }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Postal Code</label>
                                        <input type="text" class="form-control" id="pin_code" name="pin_code" value="{{ $information->pin_code }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                      <label>Phone Number</label>
                                        <input type="text" class="form-control" id="phone_number" name="phone_number" value="{{ $information->phone_number }}">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Department <span class="text-danger">*</span></label>
                                        <select class="select" id="department" name="department">
                                            <option value="{{ $information->department }}" {{ ( $information->department == $information->department) ? 'selected' : '' }}>{{ $information->department }} </option>
                                            <option value="Web Development">Web Development</option>
                                            <option value="IT Management">IT Management</option>
                                            <option value="Marketing">Marketing</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Designation <span class="text-danger">*</span></label>
                                        <select class="select" id="" name="designation">
                                            <option value="{{ $information->designation }}" {{ ( $information->designation == $information->designation) ? 'selected' : '' }}>{{ $information->designation }} </option>
                                            <option value="Web Designer">Web Designer</option>
                                            <option value="Web Developer">Web Developer</option>
                                            <option value="Android Developer">Android Developer</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Reports To <span class="text-danger">*</span></label>
                                        <select class="select" id="" name="reports_to">
                                            <option value="{{ $information->reports_to }}" {{ ( $information->reports_to == $information->reports_to) ? 'selected' : '' }}>{{ $information->reports_to }} </option>
                                            @foreach ($user as $users )
                                            <option value="{{ $users->name }}">{{ $users->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="submit-section">
                                <button type="submit" class="btn btn-primary submit-btn">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Profile Modal -->
        @else
        <!-- Profile Modal -->
        <div id="profile_info" class="modal custom-modal fade" role="dialog">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Profile Information</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('profile/information/save') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="profile-img-wrap edit-img">
                                        <img class="inline-block" src="{{ Auth::user()->avatar_url }}" alt="{{ Auth::user()->name }}">
                                    </div>
                                    <div class="profile-avatar-upload">
                                        <label class="mb-1 d-block">Profile Picture</label>
                                        <input class="form-control" type="file" name="images" accept="image/*">
                                        <small class="text-muted d-block mt-1">JPG, PNG, or WEBP. Max 5MB.</small>
                                        <input type="hidden" name="hidden_image" value="{{ Auth::user()->avatar }}">
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label>Full Name</label>
                                                <input type="text" class="form-control" id="name" name="name" value="{{ Auth::user()->name }}">
                                                <input type="hidden" class="form-control" id="user_id" name="user_id" value="{{ Auth::user()->user_id }}">
                                                <input type="hidden" class="form-control" id="email" name="email" value="{{ Auth::user()->email }}">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Birth Date</label>
                                                <div class="cal-icon">
                                                    <input class="form-control datetimepicker" type="text" id="birthDate" name="birthDate">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Gender</label>
                                                <select class="select form-control" id="gender" name="gender">
                                                    <option value="Male">Male</option>
                                                    <option value="Female">Female</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label>Address</label>
                                        <input type="text" class="form-control" id="address" name="address">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>State</label>
                                        <input type="text" class="form-control" id="state" name="state">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Country</label>
                                        <input type="text" class="form-control" id="" name="country">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Postal Code</label>
                                        <input type="text" class="form-control" id="pin_code" name="pin_code">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Phone Number</label>
                                        <input type="text" class="form-control" id="phoneNumber" name="phone_number">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Department <span class="text-danger">*</span></label>
                                        <select class="select" id="department" name="department">
                                            <option selected disabled>Select Department</option>
                                            <option value="Web Development">Web Development</option>
                                            <option value="IT Management">IT Management</option>
                                            <option value="Marketing">Marketing</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Designation <span class="text-danger">*</span></label>
                                        
                                        <select class="select" id="" name="designation">
                                            <option selected disabled>Select Designation</option>
                                            <option value="Web Designer">Web Designer</option>
                                            <option value="Web Developer">Web Developer</option>
                                            <option value="Android Developer">Android Developer</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Reports To <span class="text-danger">*</span></label>
                                        <select class="select" id="" name="reports_to">
                                            <option selected disabled>-- select --</option>
                                            @foreach ($user as $users )
                                            <option value="{{ $users->name }}">{{ $users->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="submit-section">
                                <button type="submit" class="btn btn-primary submit-btn">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Profile Modal -->
        @endif

        <!-- Bank information Modal -->
        @if($canEditBankInfo)
        <div id="bank_information_modal" class="modal custom-modal fade" role="dialog">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Bank Information</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('bank/information/save') }}" method="POST">
                            @csrf
                            <input type="hidden" class="form-control" name="user_id" value="{{ $displayUserId }}" readonly>
                            <div class="row">
                                <div class="col-md-12">
                                    <h5 class="mb-2">Primary Account</h5>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Bank name</label>
                                        @if(!empty($bankInformation?->primary_bank_name) || !empty($bankInformation?->bank_name))
                                            <input type="text" class="form-control @error('primary_bank_name') is-invalid @enderror" name="primary_bank_name" value="{{ $bankInformation?->primary_bank_name ?: $bankInformation?->bank_name }}">
                                        @else 
                                            <input type="text" class="form-control @error('primary_bank_name') is-invalid @enderror" name="primary_bank_name" value="{{ old('primary_bank_name') }}">
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Bank account No</label>
                                        @if(!empty($bankInformation?->primary_bank_account_no) || !empty($bankInformation?->bank_account_no))
                                            <input type="text" class="form-control @error('primary_bank_account_no') is-invalid @enderror" name="primary_bank_account_no" value="{{ $bankInformation?->primary_bank_account_no ?: $bankInformation?->bank_account_no }}">
                                        @else 
                                            <input type="text" class="form-control @error('primary_bank_account_no') is-invalid @enderror" name="primary_bank_account_no" oninput="this.value = this.value.replace(/[^0-9.]/g, ''); this.value = this.value.replace(/(\..*)\./g, '$1');" value="{{ old('primary_bank_account_no') }}">
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Bank Code (NIP)</label>
                                        @if(!empty($bankInformation?->primary_ifsc_code) || !empty($bankInformation?->ifsc_code))
                                            <input type="text" class="form-control @error('primary_ifsc_code') is-invalid @enderror" name="primary_ifsc_code" value="{{ $bankInformation?->primary_ifsc_code ?: $bankInformation?->ifsc_code }}">
                                        @else 
                                            <input type="text" class="form-control @error('primary_ifsc_code') is-invalid @enderror" name="primary_ifsc_code" value="{{ old('primary_ifsc_code') }}">
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>PAN No</label>
                                        @if(!empty($bankInformation?->primary_pan_no) || !empty($bankInformation?->pan_no))
                                            <input type="text" class="form-control @error('primary_pan_no') is-invalid @enderror" name="primary_pan_no" value="{{ $bankInformation?->primary_pan_no ?: $bankInformation?->pan_no }}">
                                        @else 
                                            <input type="text" class="form-control @error('primary_pan_no') is-invalid @enderror" name="primary_pan_no" value="{{ old('primary_pan_no') }}">
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-12 mt-2">
                                    <h5 class="mb-2">Secondary Account (Optional)</h5>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Bank name</label>
                                        <input type="text" class="form-control @error('secondary_bank_name') is-invalid @enderror" name="secondary_bank_name" value="{{ old('secondary_bank_name', $bankInformation?->secondary_bank_name ?? '') }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Bank account No</label>
                                        <input type="text" class="form-control @error('secondary_bank_account_no') is-invalid @enderror" name="secondary_bank_account_no" value="{{ old('secondary_bank_account_no', $bankInformation?->secondary_bank_account_no ?? '') }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Bank Code (NIP)</label>
                                        <input type="text" class="form-control @error('secondary_ifsc_code') is-invalid @enderror" name="secondary_ifsc_code" value="{{ old('secondary_ifsc_code', $bankInformation?->secondary_ifsc_code ?? '') }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>PAN No</label>
                                        <input type="text" class="form-control @error('secondary_pan_no') is-invalid @enderror" name="secondary_pan_no" value="{{ old('secondary_pan_no', $bankInformation?->secondary_pan_no ?? '') }}">
                                    </div>
                                </div>
                            </div>
                            <div class="submit-section">
                                <button type="submit" class="btn btn-primary submit-btn">Update</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @endif
        <!-- /Bank information Modal -->
    
        @if (!empty($userInformation))
        <!-- Personal Info Modal -->
        <div id="personal_info_modal" class="modal custom-modal fade" role="dialog">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Personal Information</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('user/information/save') }}" method="POST">
                            @csrf
                            <input type="hidden" class="form-control" name="user_id" value="{{ $displayUserId }}" readonly>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Passport No</label>
                                        <input type="text" class="form-control @error('passport_no') is-invalid @enderror" name="passport_no" value="{{ $userInformation->passport_no }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Passport Expiry Date</label>
                                        <div class="cal-icon">
                                            <input class="form-control datetimepicker @error('passport_expiry_date') is-invalid @enderror" type="text" name="passport_expiry_date" value="{{ $userInformation->passport_expiry_date }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Tel</label>
                                        <input class="form-control @error('tel') is-invalid @enderror" type="text" name="tel" value="{{ $userInformation->tel }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Nationality <span class="text-danger">*</span></label>
                                        <input class="form-control @error('nationality') is-invalid @enderror" type="text" name="nationality" value="{{ $userInformation->nationality }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Religion</label>
                                        <div class="form-group">
                                            <input class="form-control @error('religion') is-invalid @enderror" type="text" name="religion" value="{{ $userInformation->religion }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Marital status <span class="text-danger">*</span></label>
                                        <select class="select form-control @error('marital_status') is-invalid @enderror" name="marital_status">
                                            <option value="{{ $userInformation->marital_status }}" {{ ( $userInformation->marital_status == $userInformation->marital_status) ? 'selected' : '' }}> {{ $userInformation->marital_status }} </option>
                                            <option value="Single">Single</option>
                                            <option value="Married">Married</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Employment of spouse</label>
                                        <input class="form-control @error('employment_of_spouse') is-invalid @enderror" type="text" name="employment_of_spouse" value="{{ $userInformation->employment_of_spouse }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>No. of children </label>
                                        <input class="form-control @error('children') is-invalid @enderror" type="text" name="children" value="{{ $userInformation->children }}">
                                    </div>
                                </div>
                            </div>
                            <div class="submit-section">
                                <button type="submit" class="btn btn-primary submit-btn">Update</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Personal Info Modal -->
        @else
         <!-- Personal Info Modal -->
        <div id="personal_info_modal" class="modal custom-modal fade" role="dialog">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Personal Information</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('user/information/save') }}" method="POST">
                            @csrf
                            <input type="hidden" class="form-control" name="user_id" value="{{ $displayUserId }}" readonly>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Passport No</label>
                                        <input type="text" class="form-control @error('passport_no') is-invalid @enderror" name="passport_no">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Passport Expiry Date</label>
                                        <div class="cal-icon">
                                            <input class="form-control datetimepicker @error('passport_expiry_date') is-invalid @enderror" type="text" name="passport_expiry_date">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Tel</label>
                                        <input class="form-control @error('tel') is-invalid @enderror" type="text" name="tel">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Nationality <span class="text-danger">*</span></label>
                                        <input class="form-control @error('nationality') is-invalid @enderror" type="text" name="nationality">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Religion</label>
                                        <div class="form-group">
                                            <input class="form-control @error('religion') is-invalid @enderror" type="text" name="religion">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Marital status <span class="text-danger">*</span></label>
                                        <select class="select form-control @error('marital_status') is-invalid @enderror" name="marital_status">
                                            <option value="Single">Single</option>
                                            <option value="Married">Married</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Employment of spouse</label>
                                        <input class="form-control @error('employment_of_spouse') is-invalid @enderror" type="text" name="employment_of_spouse">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>No. of children </label>
                                        <input class="form-control @error('children') is-invalid @enderror" type="text" name="children">
                                    </div>
                                </div>
                            </div>
                            <div class="submit-section">
                                <button type="submit" class="btn btn-primary submit-btn">Update</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Personal Info Modal -->
        @endif
        
        <!-- Family Info Modal -->
        <div id="family_info_modal" class="modal custom-modal fade" role="dialog">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"> Family Informations</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form>
                            <div class="form-scroll">
                                <div class="card">
                                    <div class="card-body">
                                        <h3 class="card-title">Family Member <a href="javascript:void(0);" class="delete-icon"><i class="fa fa-trash-o"></i></a></h3>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Name <span class="text-danger">*</span></label>
                                                    <input class="form-control" type="text">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Relationship <span class="text-danger">*</span></label>
                                                    <input class="form-control" type="text">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Date of birth <span class="text-danger">*</span></label>
                                                    <input class="form-control" type="text">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Phone <span class="text-danger">*</span></label>
                                                    <input class="form-control" type="text">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="card">
                                    <div class="card-body">
                                        <h3 class="card-title">Education Informations <a href="javascript:void(0);" class="delete-icon"><i class="fa fa-trash-o"></i></a></h3>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Name <span class="text-danger">*</span></label>
                                                    <input class="form-control" type="text">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Relationship <span class="text-danger">*</span></label>
                                                    <input class="form-control" type="text">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Date of birth <span class="text-danger">*</span></label>
                                                    <input class="form-control" type="text">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Phone <span class="text-danger">*</span></label>
                                                    <input class="form-control" type="text">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="add-more">
                                            <a href="javascript:void(0);"><i class="fa fa-plus-circle"></i> Add More</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="submit-section">
                                <button class="btn btn-primary submit-btn">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Family Info Modal -->
        
        <!-- Emergency Contact Modal -->
        <div id="emergency_contact_modal" class="modal custom-modal fade" role="dialog">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Personal Information</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="validation" action="{{ route('user/profile/emergency/contact/save') }}" method="POST">
                            @csrf
                            <input type="hidden" class="form-control" name="user_id" value="{{ $displayUserId }}">
                            <div class="card">
                                <div class="card-body">
                                    <h3 class="card-title">Primary Contact</h3>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Name <span class="text-danger">*</span></label>
                                                @if (!empty($emergencyContact->name_primary))
                                                <input type="text" class="form-control" name="name_primary" value="{{ $emergencyContact->name_primary }}">
                                                @else
                                                <input type="text" class="form-control" name="name_primary">
                                                @endif
                                            </li>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Relationship <span class="text-danger">*</span></label>
                                                @if (!empty($emergencyContact->relationship_primary))
                                                <input type="text" class="form-control" name="relationship_primary" value="{{ $emergencyContact->relationship_primary }}">
                                                @else
                                                <input type="text" class="form-control" name="relationship_primary">
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Phone <span class="text-danger">*</span></label>
                                                @if (!empty($emergencyContact->phone_primary))
                                                <input type="text" class="form-control" name="phone_primary" value="{{ $emergencyContact->phone_primary }}">
                                                @else
                                                <input type="text" class="form-control" name="phone_primary">
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Phone 2</label>
                                                @if (!empty($emergencyContact->phone_2_primary))
                                                <input type="text" class="form-control" name="phone_2_primary" value="{{ $emergencyContact->phone_2_primary }}">
                                                @else
                                                <input type="text" class="form-control" name="phone_2_primary">
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card">
                                <div class="card-body">
                                    <h3 class="card-title">Secondary Contact</h3>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Name <span class="text-danger">*</span></label>
                                                @if (!empty($emergencyContact->name_secondary))
                                                <input type="text" class="form-control" name="name_secondary" value="{{ $emergencyContact->name_secondary }}">
                                                @else
                                                <input type="text" class="form-control" name="name_secondary">
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Relationship <span class="text-danger">*</span></label>
                                                @if (!empty($emergencyContact->relationship_secondary))
                                                <input type="text" class="form-control" name="relationship_secondary" value="{{ $emergencyContact->relationship_secondary }}">
                                                @else
                                                <input type="text" class="form-control" name="relationship_secondary">
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Phone <span class="text-danger">*</span></label>
                                                @if (!empty($emergencyContact->phone_secondary))
                                                <input type="text" class="form-control" name="phone_secondary" value="{{ $emergencyContact->phone_secondary }}">
                                                @else
                                                <input type="text" class="form-control" name="phone_secondary">
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Phone 2</label>
                                                @if (!empty($emergencyContact->phone_2_secondary))
                                                <input type="text" class="form-control" name="phone_2_secondary" value="{{ $emergencyContact->phone_2_secondary }}">
                                                @else
                                                <input type="text" class="form-control" name="phone_2_secondary">
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="submit-section">
                                <button type="submit" class="btn btn-primary submit-btn">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- /Emergency Contact Modal -->
        
        <!-- /Page Content -->
    </div>
    @section('script')
    <script>
        $('#validation').validate({  
            rules: {  
                name_primary: 'required',  
                relationship_primary: 'required',  
                phone_primary: 'required',  
                phone_2_primary: 'required',  
                name_secondary: 'required',  
                relationship_secondary: 'required',  
                phone_secondary: 'required',  
                phone_2_secondary: 'required',  
            },  
            messages: {
                name_primary: 'Please input name primary',  
                relationship_primary: 'Please input relationship primary',  
                phone_primary: 'Please input phone primary',  
                phone_2_primary: 'Please input phone 2 primary',  
                name_secondary: 'Please input name secondary',  
                relationship_secondary: 'Please input relationship secondary',  
                phone_secondaryr: 'Please input phone secondary',  
                phone_2_secondary: 'Please input phone 2 secondary',  
            },  
            submitHandler: function(form) {  
                form.submit();
            }  
        });  

        function nextIndex(containerSelector, rowSelector) {
            return document.querySelectorAll(containerSelector + ' ' + rowSelector).length;
        }

        function bindRemoveButtons() {
            document.querySelectorAll('.remove-row').forEach(function (button) {
                button.onclick = function () {
                    const row = this.closest('.row');
                    if (row) {
                        row.remove();
                    }
                };
            });
        }

        document.getElementById('add-education-row')?.addEventListener('click', function () {
            const index = nextIndex('#education-rows', '.education-row');
            const html = `
                <div class="row border rounded p-2 mb-2 education-row">
                    <div class="col-md-4"><input class="form-control" name="educations[${index}][institution]" placeholder="Institution"></div>
                    <div class="col-md-4"><input class="form-control" name="educations[${index}][degree]" placeholder="Degree"></div>
                    <div class="col-md-4"><input class="form-control" name="educations[${index}][field_of_study]" placeholder="Field of Study"></div>
                    <div class="col-md-3 mt-2"><input type="date" class="form-control" name="educations[${index}][start_date]"></div>
                    <div class="col-md-3 mt-2"><input type="date" class="form-control" name="educations[${index}][end_date]"></div>
                    <div class="col-md-4 mt-2"><input class="form-control" name="educations[${index}][grade]" placeholder="Grade"></div>
                    <div class="col-md-2 mt-2"><button type="button" class="btn btn-outline-danger btn-sm remove-row">Remove</button></div>
                </div>
            `;
            document.getElementById('education-rows')?.insertAdjacentHTML('beforeend', html);
            bindRemoveButtons();
        });

        document.getElementById('add-experience-row')?.addEventListener('click', function () {
            const index = nextIndex('#experience-rows', '.experience-row');
            const html = `
                <div class="row border rounded p-2 mb-2 experience-row">
                    <div class="col-md-4"><input class="form-control" name="experiences[${index}][company_name]" placeholder="Company"></div>
                    <div class="col-md-4"><input class="form-control" name="experiences[${index}][job_title]" placeholder="Job Title"></div>
                    <div class="col-md-4"><input class="form-control" name="experiences[${index}][location]" placeholder="Location"></div>
                    <div class="col-md-3 mt-2"><input type="date" class="form-control" name="experiences[${index}][start_date]"></div>
                    <div class="col-md-3 mt-2"><input type="date" class="form-control" name="experiences[${index}][end_date]"></div>
                    <div class="col-md-2 mt-2 d-flex align-items-center"><label class="mb-0"><input type="checkbox" name="experiences[${index}][is_current]" value="1"> Current</label></div>
                    <div class="col-md-4 mt-2"><input class="form-control" name="experiences[${index}][summary]" placeholder="Summary"></div>
                    <div class="col-md-2 mt-2"><button type="button" class="btn btn-outline-danger btn-sm remove-row">Remove</button></div>
                </div>
            `;
            document.getElementById('experience-rows')?.insertAdjacentHTML('beforeend', html);
            bindRemoveButtons();
        });

        document.getElementById('add-reference-row')?.addEventListener('click', function () {
            const index = nextIndex('#reference-rows', '.reference-row');
            const html = `
                <div class="row border rounded p-2 mb-2 reference-row">
                    <div class="col-md-4"><input class="form-control" name="references[${index}][referee_name]" placeholder="Referee Name"></div>
                    <div class="col-md-4"><input class="form-control" name="references[${index}][relationship]" placeholder="Relationship"></div>
                    <div class="col-md-4"><input class="form-control" name="references[${index}][company_name]" placeholder="Company"></div>
                    <div class="col-md-3 mt-2"><input class="form-control" name="references[${index}][job_title]" placeholder="Job Title"></div>
                    <div class="col-md-3 mt-2"><input class="form-control" name="references[${index}][email]" placeholder="Email"></div>
                    <div class="col-md-3 mt-2"><input class="form-control" name="references[${index}][phone]" placeholder="Phone"></div>
                    <div class="col-md-3 mt-2"><input class="form-control" name="references[${index}][years_known]" placeholder="Years Known"></div>
                    <div class="col-md-2 mt-2"><button type="button" class="btn btn-outline-danger btn-sm remove-row">Remove</button></div>
                </div>
            `;
            document.getElementById('reference-rows')?.insertAdjacentHTML('beforeend', html);
            bindRemoveButtons();
        });

        bindRemoveButtons();

    </script>
    @endsection
@endsection
