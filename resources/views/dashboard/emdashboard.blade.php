@extends('layouts.master')
@section('content')
    @php
        $calendarBase = \Carbon\Carbon::now()->startOfMonth();
        $calendarMonthLabel = $calendarBase->format('F Y');
        $gridStart = $calendarBase->copy()->startOfWeek(\Carbon\Carbon::MONDAY);
        $gridEnd = $calendarBase->copy()->endOfMonth()->endOfWeek(\Carbon\Carbon::SUNDAY);
        $dayCursor = $gridStart->copy();
        $today = \Carbon\Carbon::today();
        $firstName = trim(strtok((string) $user->name, ' ')) ?: $user->name;
        $selectedDate = $upcomingHoliday
            ? \Carbon\Carbon::parse($upcomingHoliday->date_holiday)->toDateString()
            : $today->toDateString();
    @endphp

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@700&display=swap');

        body.employee-dashboard-shell {
            background: #fcfcfd;
        }

        body.employee-dashboard-shell .header {
            display: none;
        }

        body.employee-dashboard-shell .sidebar {
            width: 240px;
            top: 0;
            bottom: 0;
            border-right: 1px solid #eae7f2;
            background: #fafafc;
            box-shadow: none;
        }

        body.employee-dashboard-shell .sidebar .sidebar-inner {
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        body.employee-dashboard-shell .sidebar .slimScrollDiv,
        body.employee-dashboard-shell .sidebar .slimScrollDiv > .sidebar-inner,
        body.employee-dashboard-shell .sidebar .slimScrollDiv > .sidebar-inner > #sidebar-menu {
            height: 100% !important;
        }

        body.employee-dashboard-shell .sidebar .menu-title {
            display: none;
        }

        body.employee-dashboard-shell .sidebar .employee-dashboard-brand {
            padding: 28px 24px 16px;
        }

        body.employee-dashboard-shell .sidebar .employee-dashboard-brand img {
            max-width: 182px;
            height: auto;
        }

        body.employee-dashboard-shell .sidebar .employee-dashboard-nav {
            margin: 0;
            padding: 10px 14px 12px;
            list-style: none;
        }

        body.employee-dashboard-shell .sidebar .employee-dashboard-nav li + li {
            margin-top: 6px;
        }

        body.employee-dashboard-shell .sidebar .employee-dashboard-nav a {
            border-radius: 12px;
            color: #5e5873 !important;
            font-size: 15px;
            line-height: 22px;
            font-weight: 500;
            padding: 11px 12px;
            display: flex;
            align-items: center;
            gap: 12px;
            position: relative;
        }

        body.employee-dashboard-shell .sidebar .employee-dashboard-nav a i {
            width: 20px;
            text-align: center;
            font-size: 20px;
            color: #8da1bc !important;
        }

        body.employee-dashboard-shell .sidebar .employee-dashboard-nav a span {
            color: #5e5873 !important;
            opacity: 1 !important;
        }

        body.employee-dashboard-shell .sidebar .employee-dashboard-nav li.active a,
        body.employee-dashboard-shell .sidebar .employee-dashboard-nav a:hover {
            color: #6d28d9 !important;
            background: #f7f2ff;
        }

        body.employee-dashboard-shell .sidebar .employee-dashboard-nav li.active a::before {
            content: '';
            position: absolute;
            left: -8px;
            top: 9px;
            bottom: 9px;
            width: 3px;
            border-radius: 999px;
            background: #6d28d9;
        }

        body.employee-dashboard-shell .sidebar .employee-dashboard-nav li.active a i,
        body.employee-dashboard-shell .sidebar .employee-dashboard-nav a:hover i {
            color: #6d28d9;
        }

        body.employee-dashboard-shell .sidebar .employee-dashboard-nav li.active a span,
        body.employee-dashboard-shell .sidebar .employee-dashboard-nav a:hover span {
            color: #6d28d9 !important;
        }

        body.employee-dashboard-shell .sidebar .employee-dashboard-support {
            margin-top: auto;
            padding: 14px;
            border-top: 1px solid #eae7f2;
        }

        body.employee-dashboard-shell .sidebar .employee-dashboard-support a {
            color: #171327;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            font-weight: 500;
            border-radius: 12px;
            border: 1px solid #eae7f2;
            background: #fff;
            padding: 11px 12px;
        }

        body.employee-dashboard-shell .sidebar .employee-dashboard-support small {
            display: block;
            color: #8c869e;
            font-size: 12px;
            line-height: 16px;
            margin-top: 1px;
        }

        body.employee-dashboard-shell .page-wrapper {
            margin-left: 240px;
            padding-top: 0;
        }

        .employee-dashboard-v3 {
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
            --pc-font-sans: 'Inter', system-ui, -apple-system, 'Segoe UI', sans-serif;

            font-family: var(--pc-font-sans);
            color: var(--pc-text-primary);
            background: var(--pc-surface-primary);
        }

        .employee-dashboard-v3 .content.container-fluid {
            max-width: 1060px;
            margin: 0 auto;
            padding: 40px 28px 36px;
        }

        .employee-dashboard-v3 .employee-topbar {
            min-height: 64px;
            border-bottom: 1px solid var(--pc-border-subtle);
            margin-bottom: 34px;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 14px;
            color: var(--pc-text-secondary);
        }

        .employee-dashboard-v3 .employee-topbar-bell {
            width: 36px;
            height: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--pc-border-subtle);
            border-radius: 999px;
            color: var(--pc-text-primary);
            background: #fff;
        }

        .employee-dashboard-v3 .employee-profile-pill {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            color: var(--pc-text-primary);
        }

        .employee-dashboard-v3 .employee-profile-avatar {
            width: 40px;
            height: 40px;
            border-radius: 999px;
            object-fit: cover;
            border: 1px solid var(--pc-border-subtle);
        }

        .employee-dashboard-v3 .employee-day {
            font-size: 16px;
            line-height: 24px;
            color: var(--pc-text-secondary);
            margin-bottom: 8px;
        }

        .employee-dashboard-v3 .employee-title {
            font-family: var(--pc-font-display);
            font-size: clamp(2rem, 4vw, 56px);
            line-height: 1.04;
            letter-spacing: -0.02em;
            margin: 0;
            color: var(--pc-text-primary);
        }

        .employee-dashboard-v3 .employee-subtitle {
            margin-top: 14px;
            max-width: 620px;
            font-size: 16px;
            line-height: 24px;
            color: var(--pc-text-secondary);
        }

        .employee-dashboard-v3 .employee-main-grid {
            margin-top: 28px;
            display: grid;
            grid-template-columns: minmax(0, 7fr) minmax(0, 5fr);
            gap: 30px;
            align-items: start;
        }

        .employee-dashboard-v3 .section-title {
            margin: 0 0 14px;
            font-size: 18px;
            font-weight: 600;
            line-height: 28px;
            color: var(--pc-text-primary);
        }

        .employee-dashboard-v3 .status-panel {
            border: 1px solid var(--pc-border-subtle);
            border-radius: var(--pc-radius-lg);
            background: #fff;
            padding: 22px;
        }

        .employee-dashboard-v3 .status-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
            gap: 16px;
            align-items: stretch;
        }

        .employee-dashboard-v3 .status-card {
            border-radius: 0;
            border: 0;
            background: transparent;
            padding: 8px 4px;
            display: flex;
            gap: 14px;
            align-items: center;
        }

        .employee-dashboard-v3 .status-card + .status-card {
            border-left: 1px solid var(--pc-border-default);
            padding-left: 18px;
        }

        .employee-dashboard-v3 .status-icon-wrap {
            width: 56px;
            height: 56px;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            flex-shrink: 0;
        }

        .employee-dashboard-v3 .status-icon-wrap.positive {
            color: var(--pc-positive);
            background: rgba(18, 185, 129, 0.12);
        }

        .employee-dashboard-v3 .status-icon-wrap.pending {
            color: var(--pc-pending);
            background: rgba(244, 163, 0, 0.14);
        }

        .employee-dashboard-v3 .status-icon-wrap.info {
            color: var(--pc-info);
            background: rgba(79, 70, 229, 0.13);
        }

        .employee-dashboard-v3 .status-label {
            margin: 0;
            font-size: 12px;
            line-height: 16px;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            font-weight: 600;
            color: var(--pc-text-secondary);
        }

        .employee-dashboard-v3 .status-value {
            margin: 3px 0 0;
            font-size: 20px;
            line-height: 28px;
            font-weight: 600;
            color: var(--pc-text-primary);
        }

        .employee-dashboard-v3 .status-detail {
            margin: 2px 0 0;
            font-size: 14px;
            line-height: 20px;
            color: var(--pc-text-secondary);
        }

        .employee-dashboard-v3 .quick-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .employee-dashboard-v3 .quick-card {
            min-height: 100px;
            border: 1px solid var(--pc-border-default);
            border-radius: var(--pc-radius-lg);
            background: #fff;
            color: var(--pc-text-primary);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 9px;
            font-size: 16px;
            line-height: 24px;
            font-weight: 500;
            transition: background-color .16s ease, border-color .16s ease, transform .16s ease;
        }

        .employee-dashboard-v3 .quick-card span {
            color: var(--pc-text-primary) !important;
        }

        .employee-dashboard-v3 .quick-card i {
            color: var(--pc-purple-600);
            font-size: 22px;
        }

        .employee-dashboard-v3 .quick-card:hover {
            background: var(--pc-surface-wash);
            border-color: var(--pc-border-strong);
            transform: translateY(-1px);
        }

        .employee-dashboard-v3 .timeline-wrap {
            margin-top: 30px;
            border-top: 1px solid var(--pc-border-default);
            padding-top: 30px;
        }

        .employee-dashboard-v3 .timeline-head,
        .employee-dashboard-v3 .calendar-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
        }

        .employee-dashboard-v3 .timeline-view-all {
            color: var(--pc-purple-600);
            font-weight: 700;
            font-size: 16px;
            line-height: 24px;
        }

        .employee-dashboard-v3 .timeline-list {
            border: 1px solid var(--pc-border-subtle);
            border-radius: var(--pc-radius-lg);
            background: #fff;
            overflow: hidden;
        }

        .employee-dashboard-v3 .timeline-item {
            display: grid;
            grid-template-columns: 64px minmax(0, 1fr) auto;
            gap: 14px;
            align-items: center;
            padding: 16px;
            border-bottom: 1px solid var(--pc-border-subtle);
            color: var(--pc-text-primary);
        }

        .employee-dashboard-v3 .timeline-item:last-child {
            border-bottom: 0;
        }

        .employee-dashboard-v3 .timeline-icon {
            width: 52px;
            height: 52px;
            border-radius: var(--pc-radius-md);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 23px;
        }

        .employee-dashboard-v3 .timeline-icon.purple {
            background: var(--pc-purple-100);
            color: var(--pc-purple-600);
        }

        .employee-dashboard-v3 .timeline-icon.mint {
            background: rgba(18, 185, 129, 0.14);
            color: var(--pc-positive);
        }

        .employee-dashboard-v3 .timeline-title {
            margin: 0;
            font-size: 20px;
            line-height: 28px;
            font-weight: 500;
            color: var(--pc-text-primary);
        }

        .employee-dashboard-v3 .timeline-detail {
            margin: 1px 0 0;
            font-size: 16px;
            line-height: 24px;
            color: var(--pc-text-secondary);
        }

        .employee-dashboard-v3 .timeline-date {
            text-align: right;
        }

        .employee-dashboard-v3 .timeline-date-main {
            margin: 0;
            font-size: 16px;
            line-height: 24px;
            font-weight: 500;
        }

        .employee-dashboard-v3 .timeline-date-sub {
            margin: 1px 0 0;
            font-size: 15px;
            line-height: 22px;
            color: var(--pc-text-secondary);
        }

        .employee-dashboard-v3 .calendar-card {
            border: 1px solid var(--pc-border-subtle);
            border-radius: var(--pc-radius-lg);
            background: #fff;
            padding: 14px;
        }

        .employee-dashboard-v3 .calendar-month {
            margin: 0;
            font-size: 30px;
            font-weight: 600;
            line-height: 28px;
            color: var(--pc-text-primary);
        }

        .employee-dashboard-v3 .calendar-grid {
            margin-top: 10px;
            display: grid;
            grid-template-columns: repeat(7, minmax(0, 1fr));
            gap: 4px;
        }

        .employee-dashboard-v3 .calendar-dow {
            text-align: center;
            font-size: 12px;
            line-height: 16px;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            font-weight: 600;
            color: var(--pc-text-secondary);
            padding: 6px 0 8px;
        }

        .employee-dashboard-v3 .calendar-day {
            height: 34px;
            border-radius: 999px;
            border: 1px solid transparent;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            width: 34px;
            font-size: 14px;
            color: var(--pc-text-primary);
            position: relative;
        }

        .employee-dashboard-v3 .calendar-day.muted {
            color: var(--pc-text-muted);
        }

        .employee-dashboard-v3 .calendar-day.selected {
            background: var(--pc-purple-600);
            color: var(--pc-text-inverse);
            font-weight: 600;
        }

        .employee-dashboard-v3 .calendar-day.today {
            border-color: var(--pc-purple-600);
        }

        .employee-dashboard-v3 .calendar-day.has-event::after {
            content: '';
            width: 5px;
            height: 5px;
            border-radius: 999px;
            background: var(--pc-purple-600);
            position: absolute;
            bottom: 4px;
            left: 50%;
            transform: translateX(-50%);
        }

        .employee-dashboard-v3 .dashboard-quote {
            margin-top: 26px;
            border-left: 3px solid var(--pc-purple-600);
            padding-left: 14px;
            max-width: 560px;
        }

        .employee-dashboard-v3 .dashboard-quote p {
            margin: 0;
            font-family: var(--pc-font-display);
            font-style: italic;
            font-size: 20px;
            line-height: 1.4;
            color: var(--pc-text-primary);
        }

        .employee-dashboard-v3 .dashboard-quote small {
            display: block;
            margin-top: 8px;
            color: var(--pc-purple-600);
            font-weight: 600;
        }

        .employee-dashboard-v3 .dashboard-support {
            margin-top: 18px;
            border: 1px solid var(--pc-border-subtle);
            border-radius: var(--pc-radius-md);
            background: #fff;
            padding: 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            color: var(--pc-text-secondary);
        }

        .employee-dashboard-v3 .dashboard-support a {
            color: var(--pc-purple-600);
            font-weight: 600;
        }

        .employee-dashboard-v3 .activity-feed {
            margin-top: 14px;
            border: 1px solid var(--pc-border-subtle);
            border-radius: var(--pc-radius-md);
            background: #fff;
            padding: 14px;
        }

        .employee-dashboard-v3 .activity-feed-item + .activity-feed-item {
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px dashed var(--pc-border-subtle);
        }

        .employee-dashboard-v3 .activity-feed-label {
            margin: 0;
            color: var(--pc-text-muted);
            font-size: 12px;
            line-height: 16px;
            letter-spacing: .08em;
            text-transform: uppercase;
            font-weight: 600;
        }

        .employee-dashboard-v3 .activity-feed-value {
            margin: 2px 0 0;
            font-size: 14px;
            line-height: 20px;
            color: var(--pc-text-secondary);
        }

        @media (max-width: 1199px) {
            .employee-dashboard-v3 .employee-main-grid {
                grid-template-columns: 1fr;
            }

            .employee-dashboard-v3 .quick-grid {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }
        }

        @media (max-width: 767px) {
            body.employee-dashboard-shell .sidebar {
                left: -240px;
            }

            body.employee-dashboard-shell .page-wrapper {
                margin-left: 0;
            }

            .employee-dashboard-v3 .content.container-fluid {
                padding: 16px 12px 24px;
            }

            .employee-dashboard-v3 .employee-topbar {
                min-height: auto;
                padding-bottom: 12px;
                margin-bottom: 18px;
            }

            .employee-dashboard-v3 .quick-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .employee-dashboard-v3 .status-grid {
                grid-template-columns: 1fr;
            }

            .employee-dashboard-v3 .status-card + .status-card {
                border-left: 0;
                border-top: 1px solid var(--pc-border-subtle);
                padding-left: 4px;
                padding-top: 14px;
            }

            .employee-dashboard-v3 .timeline-item {
                grid-template-columns: 52px minmax(0, 1fr);
            }

                .employee-dashboard-v3 .timeline-date {
                    text-align: left;
                    grid-column: 2;
                    margin-top: 4px;
                }

                .employee-dashboard-v3 .calendar-head {
                    align-items: flex-start;
                    flex-direction: column;
                    gap: 8px;
                }
        }
    </style>

    <div class="page-wrapper employee-dashboard-v3">
        <div class="content container-fluid">
            @include('employees.partials.employee-topbar', ['context' => 'Self-service workspace'])

            <div>
                <p class="employee-day">{{ $todayLabel }}</p>
                <h1 class="employee-title">Welcome, {{ $firstName }}</h1>
                <p class="employee-subtitle">Here’s what’s happening with your work today. Track attendance, leave, payroll access, and submissions from one calm workspace.</p>
            </div>

            <div class="employee-main-grid">
                <div>
                    <h2 class="section-title">Current status</h2>
                    <div class="status-panel">
                        @php
                            $statusPrimary = collect($statusSignals)->take(2);
                        @endphp
                        @if ($statusPrimary->count())
                            <div class="status-grid">
                            @foreach ($statusPrimary as $signal)
                                <div class="status-card">
                                    <span class="status-icon-wrap {{ $signal['tone'] }}">
                                        <i class="{{ $signal['icon'] }}"></i>
                                    </span>
                                    <div>
                                        <p class="status-label">{{ $signal['label'] }}</p>
                                        <p class="status-value">{{ $signal['value'] }}</p>
                                        <p class="status-detail">{{ $signal['detail'] }}</p>
                                    </div>
                                </div>
                            @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="timeline-wrap">
                        <div class="timeline-head">
                            <h2 class="section-title mb-0">Upcoming</h2>
                            <a href="{{ route('employee/holidays') }}" class="timeline-view-all">View all <i class="la la-arrow-right"></i></a>
                        </div>

                        <div class="timeline-list">
                            @forelse ($upcomingItems as $item)
                                <a href="{{ $item['route'] }}" class="timeline-item">
                                    <span class="timeline-icon {{ $item['tone'] }}">
                                        <i class="{{ $item['icon'] }}"></i>
                                    </span>
                                    <div>
                                        <p class="timeline-title">{{ $item['title'] }}</p>
                                        <p class="timeline-detail">{{ $item['detail'] }}</p>
                                    </div>
                                    <div class="timeline-date">
                                        <p class="timeline-date-main">{{ $item['date'] }}</p>
                                        <p class="timeline-date-sub">{{ $item['time'] }}</p>
                                    </div>
                                </a>
                            @empty
                                <div class="timeline-item">
                                    <span class="timeline-icon purple"><i class="la la-calendar"></i></span>
                                    <div>
                                        <p class="timeline-title">No upcoming items</p>
                                        <p class="timeline-detail">You are up to date for now.</p>
                                    </div>
                                    <div class="timeline-date">
                                        <p class="timeline-date-main">-</p>
                                        <p class="timeline-date-sub">-</p>
                                    </div>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <div class="dashboard-quote">
                        <p>“Progress is built in the small, consistent steps we take every day.”</p>
                        <small>- Purple Crayola</small>
                    </div>
                </div>

                <div>
                    <h2 class="section-title">Quick actions</h2>
                    <div class="quick-grid">
                        @foreach ($quickActions as $action)
                            <a href="{{ $action['route'] }}" class="quick-card" aria-label="{{ $action['label'] }}">
                                <i class="{{ $action['icon'] }}"></i>
                                <span>{{ $action['label'] }}</span>
                            </a>
                        @endforeach
                        <a href="#" class="quick-card employee-people-ops-trigger" data-toggle="modal" data-target="#employeePeopleOpsModal" aria-label="Contact People Ops">
                            <i class="la la-commenting-o"></i>
                            <span>Contact People Ops</span>
                        </a>
                    </div>

                    <div class="calendar-head mt-3">
                        <h2 class="section-title mb-0">{{ $calendarMonthLabel }}</h2>
                        <span class="text-muted">{{ $todayDate }}</span>
                    </div>
                    <div class="calendar-card">
                        <div class="calendar-grid">
                            @foreach (['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'] as $dow)
                                <div class="calendar-dow">{{ $dow }}</div>
                            @endforeach

                            @while ($dayCursor <= $gridEnd)
                                @php
                                    $dateKey = $dayCursor->toDateString();
                                    $isCurrentMonth = $dayCursor->month === $calendarBase->month;
                                    $isToday = $dateKey === $today->toDateString();
                                    $isSelected = $dateKey === $selectedDate;
                                    $hasEvent = $upcomingHoliday && $dateKey === \Carbon\Carbon::parse($upcomingHoliday->date_holiday)->toDateString();
                                @endphp
                                <div>
                                    <span class="calendar-day {{ !$isCurrentMonth ? 'muted' : '' }} {{ $isToday ? 'today' : '' }} {{ $isSelected ? 'selected' : '' }} {{ $hasEvent ? 'has-event' : '' }}">
                                        {{ $dayCursor->day }}
                                    </span>
                                </div>
                                @php($dayCursor->addDay())
                            @endwhile
                        </div>
                    </div>

                    <div class="activity-feed">
                        @forelse ($activityFeed as $item)
                            <div class="activity-feed-item">
                                <p class="activity-feed-label">{{ $item['label'] }}</p>
                                <p class="activity-feed-value">{{ $item['value'] }}</p>
                            </div>
                        @empty
                            <div class="activity-feed-item">
                                <p class="activity-feed-label">Activity</p>
                                <p class="activity-feed-value">No recent activity available yet.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
