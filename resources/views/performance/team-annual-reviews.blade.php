@extends('layouts.master')

@section('content')
    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">Team Annual Appraisals</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Team Annual Appraisals</li>
                        </ul>
                    </div>
                </div>
            </div>
            <style>
                .wf-step-dot {
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    min-width: 20px;
                    height: 20px;
                    border-radius: 999px;
                    font-size: 11px;
                    font-weight: 700;
                    border: 1px solid transparent;
                }
                .wf-step-dot-current {
                    background: rgba(var(--pc-workflow-current-rgb), 0.14);
                    color: var(--pc-workflow-current);
                    border-color: rgba(var(--pc-workflow-current-rgb), 0.3);
                }
                .wf-step-dot-completed {
                    background: rgba(var(--pc-workflow-completed-rgb), 0.14);
                    color: var(--pc-workflow-completed);
                    border-color: rgba(var(--pc-workflow-completed-rgb), 0.3);
                }
                .wf-step-dot-pending {
                    background: rgba(var(--pc-workflow-pending-rgb), 0.24);
                    color: rgba(var(--pc-dark-rgb), 0.72);
                    border-color: rgba(var(--pc-dark-rgb), 0.08);
                }
            </style>

            <div class="card mb-3">
                <div class="card-body">
                    <form action="{{ route('performance/team/annual-reviews') }}" method="GET" class="row">
                        <div class="col-md-3">
                            <label>Year</label>
                            <select class="form-control" name="year">
                                @foreach($years as $availableYear)
                                    <option value="{{ $availableYear }}" {{ (int) $year === (int) $availableYear ? 'selected' : '' }}>{{ $availableYear }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>Status</label>
                            <select class="form-control" name="status">
                                @foreach(['draft' => 'Draft', 'self_submitted' => 'Self Submitted', 'manager_finalized' => 'Manager Finalized'] as $statusValue => $statusLabel)
                                    <option value="{{ $statusValue }}" {{ $status === $statusValue ? 'selected' : '' }}>{{ $statusLabel }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>Last Activity Action</label>
                            <select class="form-control" name="activity_action">
                                <option value="all" {{ ($activityAction ?? 'all') === 'all' ? 'selected' : '' }}>All actions</option>
                                @foreach(($activityActionOptions ?? []) as $actionOption)
                                    <option value="{{ $actionOption }}" {{ ($activityAction ?? 'all') === $actionOption ? 'selected' : '' }}>
                                        {{ ucwords(str_replace('_', ' ', $actionOption)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label>Workflow Step</label>
                            <select class="form-control" name="workflow_step">
                                <option value="all" {{ ($workflowStepFilter ?? 'all') === 'all' ? 'selected' : '' }}>All steps</option>
                                @foreach(($workflowSteps ?? []) as $stepOption)
                                    <option value="{{ $stepOption }}" {{ ($workflowStepFilter ?? 'all') === $stepOption ? 'selected' : '' }}>
                                        {{ ucwords(str_replace('_', ' ', $stepOption)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-1 align-self-end">
                            <button type="submit" class="btn btn-success">Apply</button>
                        </div>
                    </form>
                    <form action="{{ route('performance/team/annual-reviews/generate') }}" method="POST" class="mt-3">
                        @csrf
                        <input type="hidden" name="year" value="{{ $year }}">
                        <button type="submit" class="btn btn-primary" onclick="return confirm('Generate annual review shells for all active employees for {{ $year }}?')">
                            Generate Annual Reviews For Active Employees ({{ $year }})
                        </button>
                    </form>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">
                    <h4 class="card-title mb-0">Queue Snapshot</h4>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-wrap align-items-center" style="gap:10px;">
                        @foreach(($workflowSteps ?? []) as $step)
                            @php($count = (int) (($queueSnapshot[$step] ?? 0)))
                            <a
                                href="{{ route('performance/team/annual-reviews', ['year' => $year, 'status' => $status, 'activity_action' => ($activityAction ?? 'all'), 'workflow_step' => $step]) }}"
                                class="text-decoration-none"
                                title="Workflow step: {{ ucwords(str_replace('_', ' ', $step)) }}"
                            >
                                <span class="badge p-2" style="background: rgba(var(--pc-dark-rgb), 0.06); color: var(--pc-dark); border: 1px solid rgba(var(--pc-dark-rgb), 0.1); font-size: 12px;">
                                    {{ ucwords(str_replace('_', ' ', $step)) }}: {{ $count }}
                                </span>
                            </a>
                        @endforeach
                    </div>
                    <small class="text-muted d-block mt-2">Counts reflect the current Year and Status filter scope.</small>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped custom-table mb-0">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Year</th>
                                    <th>Status</th>
                                    <th>Workflow Step</th>
                                    <th>Last Workflow Activity</th>
                                    <th>Self Submitted</th>
                                    <th>Manager Final Score</th>
                                    <th class="text-right">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($reviews as $review)
                                    <tr>
                                        <td>
                                            <div class="font-weight-bold">{{ $review->employee_name }}</div>
                                            <div class="text-muted">{{ $review->department ?: '-' }} · {{ $review->position ?: '-' }}</div>
                                        </td>
                                        <td>{{ $review->review_year }}</td>
                                        <td>{{ ucfirst(str_replace('_', ' ', $review->status)) }}</td>
                                        <td>
                                            <div class="mb-1">
                                                <strong>{{ ucwords(str_replace('_', ' ', $review->workflow_step ?? 'self_draft')) }}</strong>
                                            </div>
                                            @php
                                                $rowCurrentStep = $review->workflow_step ?? 'self_draft';
                                                $rowCurrentIndex = array_search($rowCurrentStep, ($workflowSteps ?? []), true);
                                                $rowCurrentIndex = $rowCurrentIndex === false ? 0 : $rowCurrentIndex;
                                            @endphp
                                            <div class="d-flex flex-wrap" style="gap:4px;">
                                                @foreach(($workflowSteps ?? []) as $wfIndex => $wfStep)
                                                    @php
                                                        $rowDone = $wfIndex < $rowCurrentIndex;
                                                        $rowCurrent = $wfIndex === $rowCurrentIndex;
                                                        $miniBadgeClass = $rowCurrent ? 'wf-step-dot-current' : ($rowDone ? 'wf-step-dot-completed' : 'wf-step-dot-pending');
                                                    @endphp
                                                    <span class="wf-step-dot {{ $miniBadgeClass }}">
                                                        {{ $wfIndex + 1 }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        </td>
                                        <td>
                                            @php($lastEvent = ($lastWorkflowEventsByReviewId[$review->id] ?? null))
                                            @if($lastEvent)
                                                <div class="font-weight-bold">{{ ucwords(str_replace('_', ' ', $lastEvent->action ?? '-')) }}</div>
                                                <div class="text-muted small">
                                                    {{ $lastEvent->actor?->name ?: ($lastEvent->actor_user_id ?: '-') }}
                                                    ·
                                                    {{ $lastEvent->created_at ? \Illuminate\Support\Carbon::parse($lastEvent->created_at)->format('d M Y h:i A') : '-' }}
                                                </div>
                                                @if(!empty($lastEvent->notes))
                                                    <div class="text-muted small">{{ \Illuminate\Support\Str::limit($lastEvent->notes, 72) }}</div>
                                                @endif
                                            @else
                                                <span class="text-muted">No workflow activity yet</span>
                                            @endif
                                        </td>
                                        <td>{{ $review->self_submitted_at ? \Illuminate\Support\Carbon::parse($review->self_submitted_at)->format('d M Y h:i A') : '-' }}</td>
                                        <td>{{ $review->manager_final_score ?? '-' }}</td>
                                        <td class="text-right">
                                            <a href="{{ route('performance/team/annual-reviews/view', $review->id) }}" class="btn btn-sm btn-primary">Open</a>
                                            <a href="{{ route('performance/team/annual-reviews/download', $review->id) }}" class="btn btn-sm btn-outline-secondary">PDF</a>

                                            @if(Auth::user()->isAdmin() && ($settings->annual_allow_admin_manual_progress ?? true))
                                                <form action="{{ route('performance/team/annual-reviews/progress', $review->id) }}" method="POST" class="d-inline-flex align-items-center mt-2">
                                                    @csrf
                                                    <select class="form-control form-control-sm mr-2" name="target_step">
                                                        <option value="next">Next Step</option>
                                                        @foreach(($workflowSteps ?? []) as $step)
                                                            @if($step !== 'self_draft')
                                                                <option value="{{ $step }}">{{ ucwords(str_replace('_', ' ', $step)) }}</option>
                                                            @endif
                                                        @endforeach
                                                    </select>
                                                    <button type="submit" class="btn btn-sm btn-outline-primary">Progress</button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="8" class="text-center text-muted">No annual reviews found for selected filters.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
