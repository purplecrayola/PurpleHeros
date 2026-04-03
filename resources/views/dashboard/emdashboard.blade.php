@extends('layouts.master')
@section('content')
    @include('employees.partials.self-service-style')
    <style>
        .employee-dashboard-v2 .employee-dashboard-hero {
            background: linear-gradient(135deg, rgba(var(--pc-dark-rgb), 0.98) 0%, rgba(var(--pc-primary-rgb), 0.9) 100%) !important;
            border: 0;
            border-radius: 20px;
            box-shadow: 0 18px 36px rgba(0, 22, 63, 0.16);
            margin-bottom: 18px !important;
        }
        .employee-dashboard-v2 .employee-dashboard-eyebrow,
        .employee-dashboard-v2 .employee-dashboard-title,
        .employee-dashboard-v2 .employee-dashboard-subtitle,
        .employee-dashboard-v2 .employee-dashboard-meta {
            color: #ffffff !important;
        }
        .employee-dashboard-v2 .employee-dashboard-eyebrow {
            color: rgba(255, 255, 255, 0.72) !important;
        }
        .employee-dashboard-v2 .employee-dashboard-subtitle,
        .employee-dashboard-v2 .employee-dashboard-meta {
            color: rgba(255, 255, 255, 0.86) !important;
        }
        .employee-dashboard-v2 .employee-dashboard-hero .card-body {
            padding: 26px 24px;
        }
        .employee-dashboard-v2 .employee-dashboard-title {
            font-size: 2.1rem;
            font-weight: 800;
            letter-spacing: -0.03em;
            margin-bottom: 8px;
        }
        .employee-dashboard-v2 .employee-dashboard-subtitle {
            max-width: 760px;
            font-size: 1rem;
            line-height: 1.55;
            margin-bottom: 10px;
        }
        .employee-dashboard-v2 .employee-dashboard-meta {
            font-weight: 600;
        }
        .employee-dashboard-v2 .employee-dashboard-actions {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        .employee-dashboard-v2 .employee-dashboard-action {
            font-weight: 700;
        }
        .employee-dashboard-v2 .employee-activity-item {
            border-radius: 14px;
            padding: 14px 16px;
        }
        .employee-dashboard-v2 .employee-status-card .card-title {
            margin-bottom: 10px;
        }
        @media (max-width: 991px) {
            .employee-dashboard-v2 .employee-dashboard-title {
                font-size: 1.75rem;
            }
        }
    </style>
    <div class="page-wrapper self-service-modern employee-dashboard-v2">
        <div class="content container-fluid employee-dashboard-page">
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">Employee Dashboard</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('em/dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Overview</li>
                        </ul>
                        <p class="section-intro">Your self-service command center for attendance, leave, payroll, and weekly work logs.</p>
                    </div>
                </div>
            </div>

            <div class="employee-dashboard-hero card">
                <div class="card-body">
                    <div class="employee-dashboard-hero__content">
                        <div class="employee-dashboard-hero__copy">
                            <span class="employee-dashboard-eyebrow">Employee workspace</span>
                            <h1 class="employee-dashboard-title">Welcome back, {{ $user->name }}</h1>
                            <p class="employee-dashboard-subtitle">Review your people records, attendance, leave, and weekly submissions from one self-service dashboard.</p>
                            <div class="employee-dashboard-meta">{{ $todayDate }} · {{ $user->department ?: 'Department pending' }} · {{ $user->position ?: 'Role pending' }}</div>
                        </div>
                        <div class="employee-dashboard-hero__avatar">
                            <div class="employee-dashboard-avatar-frame">
                                <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row employee-dashboard-grid">
                <div class="col-xl-8 col-lg-7">
                    <div class="employee-dashboard-metrics mb-4">
                        @foreach ($metrics as $metric)
                            <div class="employee-metric-card card panel-card">
                                <div class="card-body">
                                    <div class="employee-metric-value">{{ $metric['value'] }}</div>
                                    <div class="employee-metric-label">{{ $metric['label'] }}</div>
                                    <div class="employee-metric-helper">{{ $metric['helper'] }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="card panel-card mb-4">
                        <div class="panel-head">
                            <h4 class="panel-title">Quick Actions</h4>
                            <span class="panel-meta">Shortcuts you use most</span>
                        </div>
                        <div class="panel-body">
                            <div class="employee-dashboard-section-head">
                                <p class="text-muted mb-0">Go straight to the self-service tasks you have access to.</p>
                            </div>
                            <div class="employee-dashboard-actions">
                                @foreach ($quickActions as $action)
                                    <a href="{{ $action['route'] }}" class="employee-dashboard-action">
                                        <i class="{{ $action['icon'] }}"></i>
                                        <span>{{ $action['label'] }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="card panel-card">
                        <div class="panel-head">
                            <h4 class="panel-title">Recent Activity</h4>
                            <span class="panel-meta">{{ $activityFeed->count() }} items</span>
                        </div>
                        <div class="panel-body">
                            <div class="employee-dashboard-section-head">
                                <p class="text-muted mb-0">A clean summary of your latest HR and operations activity.</p>
                            </div>
                            <div class="employee-activity-list">
                                @forelse ($activityFeed as $item)
                                    <div class="employee-activity-item">
                                        <div class="employee-activity-label">{{ $item['label'] }}</div>
                                        <div class="employee-activity-value">{{ $item['value'] }}</div>
                                    </div>
                                @empty
                                    <div class="employee-empty-state">No employee activity is recorded yet.</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-4 col-lg-5">
                    <div class="employee-dashboard-stack">
                        <div class="card employee-status-card panel-card">
                            <div class="card-body">
                                <h3 class="card-title">Attendance snapshot</h3>
                                @if ($latestAttendance)
                                    <div class="employee-status-value">{{ $latestAttendance->status }}</div>
                                    <div class="employee-status-meta">{{ \Carbon\Carbon::parse($latestAttendance->attendance_date)->format('d M Y') }}</div>
                                    <div class="employee-status-note">Check in: {{ $latestAttendance->check_in ?: 'Not recorded' }} · Check out: {{ $latestAttendance->check_out ?: 'Not recorded' }}</div>
                                @else
                                    <div class="employee-empty-state">No attendance records yet.</div>
                                @endif
                            </div>
                        </div>

                        <div class="card employee-status-card panel-card">
                            <div class="card-body">
                                <h3 class="card-title">Leave snapshot</h3>
                                @if ($latestLeave)
                                    <div class="employee-status-value">{{ $latestLeave->leave_type }}</div>
                                    <div class="employee-status-meta">{{ \Carbon\Carbon::parse($latestLeave->from_date)->format('d M Y') }} to {{ \Carbon\Carbon::parse($latestLeave->to_date)->format('d M Y') }}</div>
                                    <div class="employee-status-note">{{ $latestLeave->leave_reason ?: 'Reason not provided' }}</div>
                                @else
                                    <div class="employee-empty-state">No leave requests yet.</div>
                                @endif
                            </div>
                        </div>

                        <div class="card employee-status-card panel-card">
                            <div class="card-body">
                                <h3 class="card-title">Upcoming holiday</h3>
                                @if ($upcomingHoliday)
                                    <div class="employee-status-value">{{ $upcomingHoliday->name_holiday }}</div>
                                    <div class="employee-status-meta">{{ \Carbon\Carbon::parse($upcomingHoliday->date_holiday)->format('l, d M Y') }}</div>
                                @else
                                    <div class="employee-empty-state">No upcoming holidays are configured.</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
