@extends('layouts.master')

@section('content')
    <div class="page-wrapper performance-annual-review-page">
        <div class="content container-fluid">
            @include('employees.partials.employee-topbar', ['context' => 'Performance workspace'])
            <div class="page-header performance-annual-review-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">Annual Performance Review</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ Auth::user()->isAdmin() ? route('home') : route('em/dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Annual Review</li>
                        </ul>
                        <p class="section-intro">Track workflow progress, review year-end summaries, and complete appraisal actions in one place.</p>
                        <p class="text-muted mb-0">{{ $subjectName }} · {{ $year }}</p>
                    </div>
                    <div class="col-auto">
                        @if($mode === 'self')
                            <a href="{{ route('performance/annual/review/download', ['year' => $year]) }}" class="btn btn-outline-primary performance-annual-review-btn">Download PDF</a>
                        @else
                            <a href="{{ route('performance/team/annual-reviews/download', ['id' => $review->id]) }}" class="btn btn-outline-primary performance-annual-review-btn">Download PDF</a>
                        @endif
                    </div>
                </div>
            </div>

            {!! Toastr::message() !!}
            <style>
                .wf-step {
                    display: inline-flex;
                    align-items: center;
                    border-radius: 999px;
                    padding: 0.45rem 0.75rem;
                    font-size: 0.82rem;
                    font-weight: 600;
                    border: 1px solid transparent;
                }
                .wf-step-current {
                    background: rgba(var(--pc-workflow-current-rgb), 0.14);
                    color: var(--pc-workflow-current);
                    border-color: rgba(var(--pc-workflow-current-rgb), 0.32);
                }
                .wf-step-completed {
                    background: rgba(var(--pc-workflow-completed-rgb), 0.14);
                    color: var(--pc-workflow-completed);
                    border-color: rgba(var(--pc-workflow-completed-rgb), 0.30);
                }
                .wf-step-pending {
                    background: rgba(var(--pc-workflow-pending-rgb), 0.2);
                    color: var(--pc-dark);
                    border-color: rgba(var(--pc-dark-rgb), 0.08);
                }
            </style>

            <div class="card mb-3">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2"><strong>Status:</strong> {{ ucfirst(str_replace('_', ' ', $review->status)) }}</div>
                        <div class="col-md-2"><strong>Workflow:</strong> {{ ucfirst(str_replace('_', ' ', $review->workflow_step ?? 'self_draft')) }}</div>
                        <div class="col-md-2"><strong>Self Obj:</strong> {{ $review->self_objectives_score ?? '-' }}</div>
                        <div class="col-md-2"><strong>Self Values:</strong> {{ $review->self_values_score ?? '-' }}</div>
                        <div class="col-md-2"><strong>Mgr Obj:</strong> {{ $review->manager_objectives_score ?? '-' }}</div>
                        <div class="col-md-2"><strong>Mgr Values:</strong> {{ $review->manager_values_score ?? '-' }}</div>
                        <div class="col-md-2 mt-2"><strong>Final:</strong> {{ $review->manager_final_score ?? '-' }}</div>
                    </div>
                </div>
            </div>

            @php
                $workflowSteps = ['self_draft'];
                if ($settings->annual_stage_manager_submit_required) {
                    $workflowSteps[] = 'manager_draft';
                }
                if ($settings->annual_stage_calibration_enabled) {
                    $workflowSteps[] = 'calibration_pending';
                }
                if ($settings->annual_stage_joint_review_enabled) {
                    $workflowSteps[] = 'joint_review_pending';
                }
                if ($settings->annual_stage_employee_ack_required) {
                    $workflowSteps[] = 'employee_ack_pending';
                }
                $workflowSteps[] = 'finalized';
                $currentWorkflowStep = $review->workflow_step ?? 'self_draft';
                $currentStepIndex = array_search($currentWorkflowStep, $workflowSteps, true);
                $currentStepIndex = $currentStepIndex === false ? 0 : $currentStepIndex;
            @endphp

            <div class="card mb-3">
                <div class="card-header">
                    <h4 class="card-title mb-0">Workflow Progress</h4>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-wrap align-items-center" style="gap:8px;">
                        @foreach($workflowSteps as $index => $step)
                            @php
                                $isDone = $index < $currentStepIndex;
                                $isCurrent = $index === $currentStepIndex;
                                $stepClass = $isCurrent ? 'wf-step-current' : ($isDone ? 'wf-step-completed' : 'wf-step-pending');
                            @endphp
                            <span class="wf-step {{ $stepClass }}">
                                {{ $index + 1 }}. {{ ucwords(str_replace('_', ' ', $step)) }}
                            </span>
                            @if(!$loop->last)
                                <span class="text-muted">→</span>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">
                    <h4 class="card-title mb-0">Workflow History</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped custom-table mb-0">
                            <thead>
                                <tr>
                                    <th>When</th>
                                    <th>Action</th>
                                    <th>From</th>
                                    <th>To</th>
                                    <th>Actor</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse(($workflowEvents ?? []) as $event)
                                    <tr>
                                        <td>{{ $event->created_at ? \Illuminate\Support\Carbon::parse($event->created_at)->format('d M Y h:i A') : '-' }}</td>
                                        <td>{{ ucwords(str_replace('_', ' ', $event->action ?? '-')) }}</td>
                                        <td>{{ $event->from_step ? ucwords(str_replace('_', ' ', $event->from_step)) : '-' }}</td>
                                        <td>{{ $event->to_step ? ucwords(str_replace('_', ' ', $event->to_step)) : '-' }}</td>
                                        <td>{{ $event->actor?->name ?: ($event->actor_user_id ?: '-') }}</td>
                                        <td>{{ $event->notes ?: '-' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted">No workflow events recorded yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header"><h4 class="card-title mb-0">Monthly Goals Rollup ({{ $year }})</h4></div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped custom-table mb-0">
                            <thead>
                                <tr>
                                    <th>Month</th>
                                    <th>Goal</th>
                                    <th>Planned</th>
                                    <th>Update</th>
                                    <th>Completion</th>
                                    <th>Manager Comment</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($monthlyGoals as $goal)
                                    <tr>
                                        <td>{{ $goal->period_number }}</td>
                                        <td>{{ $goal->title }}</td>
                                        <td>{{ $goal->planned_tasks }}</td>
                                        <td>{{ $goal->end_period_update ?: '-' }}</td>
                                        <td>{{ $goal->completion_percent !== null ? $goal->completion_percent . '%' : '-' }}</td>
                                        <td>{{ $goal->manager_comment ?: '-' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted">No monthly goals found for this year.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            @if($mode === 'self')
                @php($selfCanEdit = ($review->workflow_step ?? 'self_draft') === 'self_draft')
                <form action="{{ route('performance/annual/review/self-save', $year) }}" method="POST">
                    @csrf
                    <div class="card mb-3">
                        <div class="card-header"><h4 class="card-title mb-0">Section 1: Annual Objectives (Self Appraisal)</h4></div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped custom-table mb-0">
                                    <thead>
                                        <tr>
                                            <th>Objective</th>
                                            <th>Period</th>
                                            <th>Weight</th>
                                            <th>Self Rating (1-5)</th>
                                            <th>Self Comment</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($objectiveRatings as $rating)
                                            <tr>
                                                <td>{{ $rating->objective?->title ?? 'Objective removed' }}</td>
                                                <td>
                                                    @if($rating->objective)
                                                        {{ \Illuminate\Support\Carbon::parse($rating->objective->period_start)->format('d M Y') }} - {{ \Illuminate\Support\Carbon::parse($rating->objective->period_end)->format('d M Y') }}
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                <td>{{ $rating->objective && $rating->objective->weight !== null ? number_format((float) $rating->objective->weight, 1) . '%' : '-' }}</td>
                                                <td><input type="number" min="1" max="5" class="form-control" name="objective_self_rating[{{ $rating->id }}]" value="{{ $rating->self_rating }}" {{ $selfCanEdit ? '' : 'readonly disabled' }}></td>
                                                <td><textarea class="form-control" rows="2" name="objective_self_comment[{{ $rating->id }}]" {{ $selfCanEdit ? '' : 'readonly disabled' }}>{{ $rating->self_comment }}</textarea></td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="5" class="text-center text-muted">No annual/quarterly/biannual objectives found.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    @if($settings->annual_section_values_enabled)
                        <div class="card mb-3">
                            <div class="card-header"><h4 class="card-title mb-0">Section 2: Values (Self Appraisal)</h4></div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped custom-table mb-0">
                                        <thead>
                                            <tr>
                                                <th>Value</th>
                                                <th>Self Rating (1-5)</th>
                                                <th>Self Comment</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($valueRatings as $rating)
                                                <tr>
                                                    <td>{{ $rating->value_label }}</td>
                                                    <td><input type="number" min="1" max="5" class="form-control" name="value_self_rating[{{ $rating->id }}]" value="{{ $rating->self_rating }}" {{ $selfCanEdit ? '' : 'readonly disabled' }}></td>
                                                    <td><textarea class="form-control" rows="2" name="value_self_comment[{{ $rating->id }}]" {{ $selfCanEdit ? '' : 'readonly disabled' }}>{{ $rating->self_comment }}</textarea></td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="card mb-3">
                        <div class="card-header"><h4 class="card-title mb-0">Self Summary</h4></div>
                        <div class="card-body">
                            <textarea class="form-control" rows="4" name="self_summary" {{ $selfCanEdit ? '' : 'readonly disabled' }}>{{ $review->self_summary }}</textarea>
                        </div>
                    </div>

                    @if($selfCanEdit)
                        <div class="text-right mb-3">
                            <button type="submit" name="submit" value="0" class="btn btn-outline-primary">Save Draft</button>
                            <button type="submit" name="submit" value="1" class="btn btn-primary">Submit Self Appraisal</button>
                        </div>
                    @else
                        <div class="alert alert-info mb-3">
                            Self section is locked at this workflow stage ({{ ucfirst(str_replace('_', ' ', $review->workflow_step ?? 'self_draft')) }}).
                        </div>
                    @endif
                </form>

                @if(($review->workflow_step ?? null) === 'employee_ack_pending')
                    <form action="{{ route('performance/annual/review/acknowledge', $year) }}" method="POST" class="text-right mb-3">
                        @csrf
                        <button type="submit" class="btn btn-success">Acknowledge and Complete Appraisal</button>
                    </form>
                @endif
            @else
                @php($managerCanEdit = $managerCanEdit ?? false)
                <form action="{{ route('performance/team/annual-reviews/view', $review->id) }}" method="POST">
                    @csrf
                    <div class="card mb-3">
                        <div class="card-header"><h4 class="card-title mb-0">Section 1: Annual Objectives (Manager Appraisal)</h4></div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped custom-table mb-0">
                                    <thead>
                                        <tr>
                                            <th>Objective</th>
                                            <th>Self Rating</th>
                                            <th>Self Comment</th>
                                            <th>Manager Rating (1-5)</th>
                                            <th>Manager Comment</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($objectiveRatings as $rating)
                                            <tr>
                                                <td>{{ $rating->objective?->title ?? 'Objective removed' }}</td>
                                                <td>{{ $rating->self_rating ?? '-' }}</td>
                                                <td>{{ $rating->self_comment ?: '-' }}</td>
                                                <td><input type="number" min="1" max="5" class="form-control" name="objective_manager_rating[{{ $rating->id }}]" value="{{ $rating->manager_rating }}" {{ $managerCanEdit ? '' : 'readonly disabled' }}></td>
                                                <td><textarea class="form-control" rows="2" name="objective_manager_comment[{{ $rating->id }}]" {{ $managerCanEdit ? '' : 'readonly disabled' }}>{{ $rating->manager_comment }}</textarea></td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="5" class="text-center text-muted">No objectives available.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    @if($settings->annual_section_values_enabled)
                        <div class="card mb-3">
                            <div class="card-header"><h4 class="card-title mb-0">Section 2: Values (Manager Appraisal)</h4></div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped custom-table mb-0">
                                        <thead>
                                            <tr>
                                                <th>Value</th>
                                                <th>Self Rating</th>
                                                <th>Self Comment</th>
                                                <th>Manager Rating (1-5)</th>
                                                <th>Manager Comment</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($valueRatings as $rating)
                                                <tr>
                                                    <td>{{ $rating->value_label }}</td>
                                                    <td>{{ $rating->self_rating ?? '-' }}</td>
                                                    <td>{{ $rating->self_comment ?: '-' }}</td>
                                                    <td><input type="number" min="1" max="5" class="form-control" name="value_manager_rating[{{ $rating->id }}]" value="{{ $rating->manager_rating }}" {{ $managerCanEdit ? '' : 'readonly disabled' }}></td>
                                                    <td><textarea class="form-control" rows="2" name="value_manager_comment[{{ $rating->id }}]" {{ $managerCanEdit ? '' : 'readonly disabled' }}>{{ $rating->manager_comment }}</textarea></td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="card mb-3">
                        <div class="card-header"><h4 class="card-title mb-0">Manager Summary</h4></div>
                        <div class="card-body">
                            <textarea class="form-control" rows="4" name="manager_summary" {{ $managerCanEdit ? '' : 'readonly disabled' }}>{{ $review->manager_summary }}</textarea>
                        </div>
                    </div>

                    @if($managerCanEdit)
                        <div class="text-right mb-3">
                            <button type="submit" name="finalize" value="0" class="btn btn-outline-primary">Save Manager Draft</button>
                            <button type="submit" name="finalize" value="1" class="btn btn-primary">Finalize Manager Appraisal</button>
                        </div>
                    @else
                        <div class="alert alert-info mb-3">Manager scoring is available only when workflow is at Manager Draft.</div>
                    @endif
                </form>
            @endif
        </div>
    </div>
@endsection

@section('style')
<style>
    body.employee-dashboard-shell .performance-annual-review-page .performance-annual-review-header {
        margin-top: 0;
        margin-bottom: 24px;
    }
    body.employee-dashboard-shell .performance-annual-review-page .performance-annual-review-btn {
        min-height: 40px;
        border-radius: 12px;
        padding: 0 18px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        line-height: 1;
    }
</style>
@endsection
