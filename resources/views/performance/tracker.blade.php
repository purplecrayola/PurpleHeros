@extends('layouts.master')

@section('content')
    <div class="page-wrapper performance-tracker-page">
        <div class="content container-fluid">
            @include('employees.partials.employee-topbar', ['context' => 'Performance workspace'])
            <div class="page-header performance-tracker-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">Performance Tracker</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ Auth::user()->isAdmin() ? route('home') : route('em/dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Performance Tracker</li>
                        </ul>
                        <p class="section-intro">Plan goals, update progress, and keep objective tracking aligned with your review cycle.</p>
                    </div>
                </div>
            </div>

            {!! Toastr::message() !!}

            <div class="card mb-3 performance-filter-card">
                <div class="card-body">
                    <form action="{{ route('performance/tracker') }}" method="GET" class="row align-items-end">
                        <div class="col-md-3 col-sm-6">
                            <label>Year</label>
                            <select name="year" class="form-control">
                                @foreach($years as $availableYear)
                                    <option value="{{ $availableYear }}" {{ (int) $year === (int) $availableYear ? 'selected' : '' }}>{{ $availableYear }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-5 col-sm-6">
                            <div class="performance-filter-actions">
                                <button type="submit" class="btn btn-success performance-filter-btn">Apply</button>
                                <a href="{{ route('performance/annual/review', ['year' => $year]) }}" class="btn btn-outline-primary performance-filter-btn">View Annual Review</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header"><h4 class="card-title mb-0">Create Weekly or Monthly Goal</h4></div>
                <div class="card-body">
                    <form action="{{ route('performance/tracker/goal/save') }}" method="POST">
                        @csrf
                        <div class="row performance-goal-row">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Type</label>
                                    <select class="form-control" name="period_type" id="goal_period_type" required>
                                        <option value="weekly">Weekly</option>
                                        <option value="monthly">Monthly</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Year</label>
                                    <input type="number" class="form-control" id="goal_period_year" name="period_year" value="{{ $year }}" min="2000" max="2100" required>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label id="period_number_label">Period Number</label>
                                    <input type="number" class="form-control" id="goal_period_number" name="period_number" min="1" max="53" placeholder="Week or Month number" required>
                                </div>
                            </div>
                            <div class="col-md-3" id="week_commencing_group">
                                <div class="form-group">
                                    <label>Week Commencing (WC)</label>
                                    <input type="date" class="form-control" id="goal_week_commencing" name="week_commencing">
                                    <small class="text-muted">Pick any date in the week. We auto-map to Monday + ISO week number.</small>
                                </div>
                            </div>
                            <div class="col-md-3" id="weekly_preview_group">
                                <div class="form-group">
                                    <label>Weekly Preview</label>
                                    <input type="text" class="form-control" id="goal_week_preview" value="WC - | Week -" readonly>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Goal Title</label>
                                    <input type="text" class="form-control" name="title" required>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Planned Tasks/Goals</label>
                                    <textarea class="form-control" rows="3" name="planned_tasks" required></textarea>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Save Goal Plan</button>
                    </form>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header"><h4 class="card-title mb-0">Objectives (Quarterly/Biannual/Annual)</h4></div>
                <div class="card-body">
                    <form action="{{ route('performance/tracker/objective/save') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>For User ID</label>
                                    <input type="text" class="form-control" name="user_id" value="{{ Auth::user()->user_id }}" {{ $isManager ? '' : 'readonly' }} required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Period Type</label>
                                    <select class="form-control" name="period_type" required>
                                        <option value="quarterly">Quarterly</option>
                                        <option value="biannual">Biannual</option>
                                        <option value="annual">Annual</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Start Date</label>
                                    <input type="date" class="form-control" name="period_start" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>End Date</label>
                                    <input type="date" class="form-control" name="period_end" required>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label>Objective Title</label>
                                    <input type="text" class="form-control" name="title" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Weight (%)</label>
                                    <input type="number" class="form-control" name="weight" min="0" max="100" placeholder="Optional">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Description</label>
                                    <textarea class="form-control" rows="2" name="description"></textarea>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-outline-primary">Save Objective Plan</button>
                    </form>

                    <hr>
                    <h5>Current Objectives</h5>
                    <div class="table-responsive">
                        <table class="table table-striped custom-table mb-0">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Type</th>
                                    <th>Period</th>
                                    <th>Weight</th>
                                    <th>Source</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($objectives as $objective)
                                    <tr>
                                        <td>{{ $objective->title }}</td>
                                        <td>{{ ucfirst($objective->period_type) }}</td>
                                        <td>{{ \Illuminate\Support\Carbon::parse($objective->period_start)->format('d M Y') }} - {{ \Illuminate\Support\Carbon::parse($objective->period_end)->format('d M Y') }}</td>
                                        <td>{{ $objective->weight !== null ? number_format((float) $objective->weight, 1) . '%' : '-' }}</td>
                                        <td>{{ ucfirst(str_replace('_', ' ', $objective->source)) }}</td>
                                        <td>{{ ucfirst($objective->status) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted">No objectives yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h4 class="card-title mb-0">Weekly & Monthly Updates</h4></div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped custom-table mb-0">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Period</th>
                                    <th>Title</th>
                                    <th>Status</th>
                                    <th>Completion</th>
                                    <th>Manager Comment</th>
                                    <th>Update</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($goals as $goal)
                                    <tr>
                                        <td>{{ ucfirst($goal->period_type) }}</td>
                                        <td>
                                            @if($goal->period_type === 'weekly')
                                                @php
                                                    $wc = \Illuminate\Support\Carbon::now()
                                                        ->setISODate((int) $goal->period_year, (int) $goal->period_number)
                                                        ->startOfWeek(\Illuminate\Support\Carbon::MONDAY)
                                                        ->format('d M Y');
                                                @endphp
                                                WC {{ $wc }} (W{{ str_pad((string) $goal->period_number, 2, '0', STR_PAD_LEFT) }})
                                            @else
                                                {{ \Illuminate\Support\Carbon::create((int) $goal->period_year, (int) $goal->period_number, 1)->format('M Y') }}
                                            @endif
                                        </td>
                                        <td>
                                            <div class="font-weight-bold">{{ $goal->title }}</div>
                                            <div class="text-muted">{{ $goal->planned_tasks }}</div>
                                        </td>
                                        <td>{{ ucfirst($goal->status) }}</td>
                                        <td>{{ $goal->completion_percent !== null ? $goal->completion_percent . '%' : '-' }}</td>
                                        <td>{{ $goal->manager_comment ?: '-' }}</td>
                                        <td>
                                            @if(in_array($goal->status, ['draft', 'submitted'], true))
                                                <form action="{{ route('performance/tracker/goal/submit', $goal->id) }}" method="POST">
                                                    @csrf
                                                    <div class="form-group mb-1">
                                                        <textarea class="form-control" name="end_period_update" rows="2" placeholder="End-of-period update" required>{{ $goal->end_period_update }}</textarea>
                                                    </div>
                                                    <div class="form-group mb-1">
                                                        <input type="number" class="form-control" name="completion_percent" min="0" max="100" value="{{ $goal->completion_percent ?? 0 }}" required>
                                                    </div>
                                                    <div class="form-group mb-1">
                                                        <input type="text" class="form-control" name="blockers" value="{{ $goal->blockers }}" placeholder="Blockers (optional)">
                                                    </div>
                                                    <button class="btn btn-sm btn-success">Submit Update</button>
                                                </form>
                                            @else
                                                <span class="text-muted">Reviewed</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="text-center text-muted">No weekly/monthly goals yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('style')
<style>
    body.employee-dashboard-shell .performance-tracker-page .performance-tracker-header {
        margin-top: 0;
        margin-bottom: 24px;
    }
    body.employee-dashboard-shell .performance-filter-card .card-body {
        padding-bottom: 18px;
    }
    body.employee-dashboard-shell .performance-filter-actions {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }
    body.employee-dashboard-shell .performance-filter-btn {
        min-height: 40px;
        border-radius: 12px;
        padding: 0 18px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        line-height: 1;
    }
    body.employee-dashboard-shell .performance-goal-row .form-control,
    body.employee-dashboard-shell .performance-goal-row .input-group .form-control {
        min-height: 40px;
        height: 40px;
    }
    body.employee-dashboard-shell .performance-goal-row textarea.form-control {
        min-height: 104px;
        height: auto;
    }
    @media (max-width: 767px) {
        body.employee-dashboard-shell .performance-filter-actions .performance-filter-btn {
            width: 100%;
        }
    }
</style>
@endsection

@section('script')
<script>
    (function () {
        const periodType = document.getElementById('goal_period_type');
        const periodYear = document.getElementById('goal_period_year');
        const periodNumber = document.getElementById('goal_period_number');
        const periodNumberLabel = document.getElementById('period_number_label');
        const wcGroup = document.getElementById('week_commencing_group');
        const wcInput = document.getElementById('goal_week_commencing');
        const previewGroup = document.getElementById('weekly_preview_group');
        const previewInput = document.getElementById('goal_week_preview');

        if (!periodType || !periodYear || !periodNumber || !periodNumberLabel || !wcGroup || !wcInput || !previewGroup || !previewInput) {
            return;
        }

        function isoFromDate(date) {
            const dt = new Date(Date.UTC(date.getFullYear(), date.getMonth(), date.getDate()));
            const dayNum = dt.getUTCDay() || 7;
            dt.setUTCDate(dt.getUTCDate() + 4 - dayNum);
            const yearStart = new Date(Date.UTC(dt.getUTCFullYear(), 0, 1));
            const week = Math.ceil((((dt - yearStart) / 86400000) + 1) / 7);
            return { week: week, year: dt.getUTCFullYear() };
        }

        function mondayOfWeek(date) {
            const dt = new Date(date);
            const day = dt.getDay();
            const diff = dt.getDate() - day + (day === 0 ? -6 : 1);
            dt.setDate(diff);
            return dt;
        }

        function fmt(date) {
            const yyyy = date.getFullYear();
            const mm = String(date.getMonth() + 1).padStart(2, '0');
            const dd = String(date.getDate()).padStart(2, '0');
            return `${yyyy}-${mm}-${dd}`;
        }

        function updateWeeklyFromDate() {
            if (!wcInput.value) {
                previewInput.value = 'WC - | Week -';
                return;
            }

            const picked = new Date(wcInput.value);
            if (Number.isNaN(picked.getTime())) {
                previewInput.value = 'WC - | Week -';
                return;
            }

            const monday = mondayOfWeek(picked);
            const iso = isoFromDate(monday);
            periodYear.value = String(iso.year);
            periodNumber.value = String(iso.week);
            previewInput.value = `WC ${fmt(monday)} | Week ${String(iso.week).padStart(2, '0')}`;
        }

        function togglePeriodUi() {
            const weekly = periodType.value === 'weekly';
            wcGroup.style.display = weekly ? '' : 'none';
            previewGroup.style.display = weekly ? '' : 'none';

            if (weekly) {
                periodNumberLabel.textContent = 'Week Number';
                periodNumber.min = '1';
                periodNumber.max = '53';
                periodNumber.readOnly = true;
                if (!wcInput.value) {
                    const today = new Date();
                    wcInput.value = fmt(today);
                }
                updateWeeklyFromDate();
            } else {
                periodNumberLabel.textContent = 'Month Number';
                periodNumber.min = '1';
                periodNumber.max = '12';
                periodNumber.readOnly = false;
                previewInput.value = '';
            }
        }

        periodType.addEventListener('change', togglePeriodUi);
        wcInput.addEventListener('change', updateWeeklyFromDate);
        togglePeriodUi();
    })();
</script>
@endsection
