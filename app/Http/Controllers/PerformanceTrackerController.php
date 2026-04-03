<?php

namespace App\Http\Controllers;

use App\Models\AnnualObjectiveRating;
use App\Models\AnnualPerformanceReview;
use App\Models\AnnualReviewWorkflowEvent;
use App\Models\AnnualValueRating;
use App\Models\CompanySettings;
use App\Models\PerformanceGoalEntry;
use App\Models\PerformanceObjective;
use App\Models\PerformanceReviewSetting;
use App\Models\User;
use App\Support\MailSettingsManager;
use Barryvdh\DomPDF\Facade\Pdf;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class PerformanceTrackerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function myTracker(Request $request)
    {
        $user = Auth::user();
        abort_unless($user !== null, 403);

        $settings = PerformanceReviewSetting::current();
        $year = (int) $request->get('year', now()->year);

        $goals = PerformanceGoalEntry::query()
            ->where('user_id', $user->user_id)
            ->where('period_year', $year)
            ->orderByDesc('period_year')
            ->orderByDesc('period_type')
            ->orderByDesc('period_number')
            ->limit(40)
            ->get();

        $objectives = PerformanceObjective::query()
            ->where('user_id', $user->user_id)
            ->whereYear('period_start', '<=', $year)
            ->whereYear('period_end', '>=', $year)
            ->orderByDesc('period_start')
            ->get();

        return view('performance.tracker', [
            'settings' => $settings,
            'goals' => $goals,
            'objectives' => $objectives,
            'year' => $year,
            'years' => range(now()->year - 2, now()->year + 1),
            'isManager' => $this->canManageTeamPerformance($user),
        ]);
    }

    public function saveGoal(Request $request)
    {
        $user = Auth::user();
        abort_unless($user !== null, 403);

        $validated = $request->validate([
            'id' => 'nullable|integer|exists:performance_goal_entries,id',
            'period_type' => 'required|in:weekly,monthly',
            'period_year' => 'required|integer|min:2000|max:2100',
            'period_number' => 'required|integer|min:1|max:53',
            'week_commencing' => 'nullable|date',
            'title' => 'required|string|max:255',
            'planned_tasks' => 'required|string|max:5000',
        ]);

        if ($validated['period_type'] === 'weekly' && ! empty($validated['week_commencing'])) {
            $weekStart = Carbon::parse($validated['week_commencing'])->startOfWeek(Carbon::MONDAY);
            $validated['period_year'] = (int) $weekStart->isoWeekYear;
            $validated['period_number'] = (int) $weekStart->isoWeek;
        }

        if ($validated['period_type'] === 'monthly' && $validated['period_number'] > 12) {
            return redirect()->back()->withErrors(['period_number' => 'Monthly entries must use month 1 to 12.'])->withInput();
        }

        $goal = isset($validated['id']) ? PerformanceGoalEntry::query()->findOrFail($validated['id']) : new PerformanceGoalEntry();

        if ($goal->exists && $goal->user_id !== $user->user_id) {
            abort(403);
        }

        if ($goal->exists && in_array($goal->status, ['submitted', 'reviewed'], true)) {
            Toastr::error('This entry has already been submitted for review and is locked.', 'Error');
            return redirect()->back();
        }

        $goal->fill([
            'user_id' => $user->user_id,
            'period_type' => $validated['period_type'],
            'period_year' => $validated['period_year'],
            'period_number' => $validated['period_number'],
            'title' => $validated['title'],
            'planned_tasks' => $validated['planned_tasks'],
            'created_by_user_id' => $goal->exists ? $goal->created_by_user_id : $user->user_id,
        ]);

        $goal->save();

        Toastr::success('Performance goal saved.', 'Success');
        return redirect()->route('performance/tracker');
    }

    public function submitGoalUpdate(Request $request, int $id)
    {
        $user = Auth::user();
        abort_unless($user !== null, 403);

        $validated = $request->validate([
            'end_period_update' => 'required|string|max:5000',
            'completion_percent' => 'required|integer|min:0|max:100',
            'blockers' => 'nullable|string|max:3000',
        ]);

        $goal = PerformanceGoalEntry::query()->findOrFail($id);
        if ($goal->user_id !== $user->user_id) {
            abort(403);
        }

        if ($goal->status === 'reviewed') {
            Toastr::error('This entry has already been reviewed by your manager.', 'Error');
            return redirect()->back();
        }

        $goal->fill([
            'end_period_update' => $validated['end_period_update'],
            'completion_percent' => $validated['completion_percent'],
            'blockers' => $validated['blockers'] ?? null,
            'status' => 'submitted',
            'submitted_at' => now(),
        ])->save();

        Toastr::success('Weekly/Monthly update submitted for review.', 'Success');
        return redirect()->route('performance/tracker');
    }

    public function saveObjective(Request $request)
    {
        $user = Auth::user();
        abort_unless($user !== null, 403);

        $settings = PerformanceReviewSetting::current();

        $validated = $request->validate([
            'user_id' => 'required|string|exists:users,user_id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'period_type' => 'required|in:quarterly,biannual,annual',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
            'weight' => 'nullable|numeric|min:0|max:100',
        ]);

        $targetUserId = $validated['user_id'];
        $isSelf = $targetUserId === $user->user_id;
        $canManage = $this->canManageTeamPerformance($user);

        if (! $isSelf && ! $canManage) {
            abort(403);
        }

        if (! $canManage && ! $settings->allow_employee_objectives) {
            Toastr::error('Employee-created objectives are disabled in settings.', 'Error');
            return redirect()->back();
        }

        if (! $isSelf && ! $settings->allow_manager_objectives) {
            Toastr::error('Manager-created objectives are disabled in settings.', 'Error');
            return redirect()->back();
        }

        $source = $canManage
            ? ($user->hasRole('Super Admin') ? 'super_admin' : 'manager')
            : 'employee';

        PerformanceObjective::query()->create([
            'user_id' => $targetUserId,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'period_type' => $validated['period_type'],
            'period_start' => $validated['period_start'],
            'period_end' => $validated['period_end'],
            'weight' => $validated['weight'] ?? null,
            'source' => $source,
            'created_by_user_id' => $user->user_id,
            'status' => 'active',
        ]);

        Toastr::success('Objective added successfully.', 'Success');
        return redirect()->route('performance/tracker');
    }

    public function teamReviews(Request $request)
    {
        $user = Auth::user();
        abort_unless($user !== null, 403);
        abort_unless($this->canManageTeamPerformance($user), 403);

        $year = (int) $request->get('year', now()->year);
        $status = (string) $request->get('status', 'submitted');

        $query = PerformanceGoalEntry::query()
            ->join('users', 'users.user_id', '=', 'performance_goal_entries.user_id')
            ->select('performance_goal_entries.*', 'users.name as employee_name', 'users.department', 'users.position')
            ->where('performance_goal_entries.period_year', $year)
            ->orderByDesc('performance_goal_entries.period_type')
            ->orderByDesc('performance_goal_entries.period_number')
            ->orderByDesc('performance_goal_entries.updated_at');

        if (in_array($status, ['draft', 'submitted', 'reviewed'], true)) {
            $query->where('performance_goal_entries.status', $status);
        }

        $entries = $query->limit(100)->get();

        return view('performance.team-reviews', [
            'entries' => $entries,
            'status' => $status,
            'year' => $year,
            'years' => range(now()->year - 2, now()->year + 1),
        ]);
    }

    public function reviewGoal(Request $request, int $id)
    {
        $user = Auth::user();
        abort_unless($user !== null, 403);
        abort_unless($this->canManageTeamPerformance($user), 403);

        $validated = $request->validate([
            'manager_comment' => 'required|string|max:5000',
            'status' => 'required|in:submitted,reviewed',
        ]);

        $goal = PerformanceGoalEntry::query()->findOrFail($id);
        $goal->manager_comment = $validated['manager_comment'];
        $goal->status = $validated['status'];
        $goal->manager_reviewed_at = $validated['status'] === 'reviewed' ? now() : null;
        $goal->save();

        Toastr::success('Manager review saved.', 'Success');
        return redirect()->route('performance/team/reviews');
    }

    public function annualReview(Request $request)
    {
        $user = Auth::user();
        abort_unless($user !== null, 403);

        $year = (int) $request->get('year', now()->year);
        $settings = PerformanceReviewSetting::current();

        $review = AnnualPerformanceReview::query()->firstOrCreate(
            ['user_id' => $user->user_id, 'review_year' => $year],
            ['status' => 'draft', 'workflow_step' => 'self_draft']
        );
        $this->ensureAnnualWorkflowStep($review, $settings);

        $objectives = $this->objectivesForYear($user->user_id, $year);
        foreach ($objectives as $objective) {
            AnnualObjectiveRating::query()->firstOrCreate([
                'annual_performance_review_id' => $review->id,
                'performance_objective_id' => $objective->id,
            ]);
        }

        $valuesCatalog = $settings->valuesCatalog();
        foreach ($valuesCatalog as $label) {
            $normalizedLabel = $this->normalizeCatalogLabel($label);
            if ($normalizedLabel === null) {
                continue;
            }

            $key = Str::of($normalizedLabel)->slug()->toString();
            AnnualValueRating::query()->firstOrCreate([
                'annual_performance_review_id' => $review->id,
                'value_key' => $key,
            ], [
                'value_label' => $normalizedLabel,
            ]);
        }

        $monthlyGoals = PerformanceGoalEntry::query()
            ->where('user_id', $user->user_id)
            ->where('period_type', 'monthly')
            ->where('period_year', $year)
            ->orderBy('period_number')
            ->get();

        $objectiveRatings = AnnualObjectiveRating::query()
            ->where('annual_performance_review_id', $review->id)
            ->with('objective')
            ->get();

        $valueRatings = AnnualValueRating::query()
            ->where('annual_performance_review_id', $review->id)
            ->orderBy('value_label')
            ->get();
        $workflowEvents = AnnualReviewWorkflowEvent::query()
            ->where('annual_performance_review_id', $review->id)
            ->with('actor:user_id,name,role_name')
            ->latest()
            ->limit(25)
            ->get();

        return view('performance.annual-review', [
            'review' => $review,
            'settings' => $settings,
            'monthlyGoals' => $monthlyGoals,
            'objectiveRatings' => $objectiveRatings,
            'valueRatings' => $valueRatings,
            'workflowEvents' => $workflowEvents,
            'year' => $year,
            'years' => range(now()->year - 2, now()->year + 1),
            'mode' => 'self',
            'subjectUser' => $user,
            'subjectName' => $user->name,
        ]);
    }

    public function saveSelfAnnualReview(Request $request, int $year)
    {
        $user = Auth::user();
        abort_unless($user !== null, 403);

        $settings = PerformanceReviewSetting::current();
        $review = AnnualPerformanceReview::query()->firstOrCreate(
            ['user_id' => $user->user_id, 'review_year' => $year],
            ['status' => 'draft', 'workflow_step' => 'self_draft']
        );
        $this->ensureAnnualWorkflowStep($review, $settings);

        if ($review->status === 'manager_finalized' || $review->workflow_step === 'finalized') {
            Toastr::error('This annual review is already finalized by your manager.', 'Error');
            return redirect()->back();
        }

        if (($review->workflow_step ?? 'self_draft') !== 'self_draft') {
            Toastr::error('Self appraisal can only be edited while workflow is at Self Draft.', 'Error');
            return redirect()->back();
        }

        $validated = $request->validate([
            'self_summary' => 'nullable|string|max:6000',
            'objective_self_rating' => 'nullable|array',
            'objective_self_rating.*' => 'nullable|integer|min:1|max:5',
            'objective_self_comment' => 'nullable|array',
            'objective_self_comment.*' => 'nullable|string|max:3000',
            'value_self_rating' => 'nullable|array',
            'value_self_rating.*' => 'nullable|integer|min:1|max:5',
            'value_self_comment' => 'nullable|array',
            'value_self_comment.*' => 'nullable|string|max:3000',
            'submit' => 'nullable|string|in:0,1',
        ]);

        DB::transaction(function () use ($validated, $review, $settings, $user, $year) {
            $review->self_summary = $validated['self_summary'] ?? null;

            $objectiveRatings = $validated['objective_self_rating'] ?? [];
            $objectiveComments = $validated['objective_self_comment'] ?? [];
            foreach ($objectiveRatings as $ratingId => $ratingValue) {
                $row = AnnualObjectiveRating::query()
                    ->where('annual_performance_review_id', $review->id)
                    ->where('id', $ratingId)
                    ->first();
                if (! $row) {
                    continue;
                }
                $row->self_rating = $ratingValue !== null && $ratingValue !== '' ? (int) $ratingValue : null;
                $row->self_comment = $objectiveComments[$ratingId] ?? null;
                $row->save();
            }

            $valueRatings = $validated['value_self_rating'] ?? [];
            $valueComments = $validated['value_self_comment'] ?? [];
            foreach ($valueRatings as $ratingId => $ratingValue) {
                $row = AnnualValueRating::query()
                    ->where('annual_performance_review_id', $review->id)
                    ->where('id', $ratingId)
                    ->first();
                if (! $row) {
                    continue;
                }
                $row->self_rating = $ratingValue !== null && $ratingValue !== '' ? (int) $ratingValue : null;
                $row->self_comment = $valueComments[$ratingId] ?? null;
                $row->save();
            }

            $review->self_objectives_score = $this->computeAverageScore(
                AnnualObjectiveRating::query()
                    ->where('annual_performance_review_id', $review->id)
                    ->pluck('self_rating')
                    ->all()
            );
            $review->self_values_score = $settings->annual_section_values_enabled
                ? $this->computeAverageScore(
                    AnnualValueRating::query()
                        ->where('annual_performance_review_id', $review->id)
                        ->pluck('self_rating')
                        ->all()
                )
                : null;

            if (($validated['submit'] ?? '0') === '1') {
                $review->status = 'self_submitted';
                $review->self_submitted_at = now();
                $nextStep = $settings->annual_stage_manager_submit_required
                    ? 'manager_draft'
                    : $this->nextAnnualWorkflowStepAfterManager($settings);
                $this->transitionAnnualWorkflow(
                    $review,
                    $nextStep,
                    'self_submitted',
                    $user,
                    'Employee submitted self appraisal for review.',
                    true,
                    route('performance/team/annual-reviews', ['year' => $year])
                );
            }

            $review->save();
        });

        Toastr::success(($validated['submit'] ?? '0') === '1' ? 'Self appraisal submitted.' : 'Self appraisal saved.', 'Success');
        return redirect()->route('performance/annual/review', ['year' => $year]);
    }

    public function teamAnnualReviews(Request $request)
    {
        $user = Auth::user();
        abort_unless($user !== null, 403);
        abort_unless($this->canManageTeamPerformance($user), 403);

        $year = (int) $request->get('year', now()->year);
        $status = (string) $request->get('status', 'self_submitted');
        $activityAction = trim((string) $request->get('activity_action', 'all'));
        $workflowStepFilter = trim((string) $request->get('workflow_step', 'all'));
        $settings = PerformanceReviewSetting::current();

        $query = AnnualPerformanceReview::query()
            ->join('users', 'users.user_id', '=', 'annual_performance_reviews.user_id')
            ->select('annual_performance_reviews.*', 'users.name as employee_name', 'users.department', 'users.position')
            ->where('annual_performance_reviews.review_year', $year)
            ->orderByDesc('annual_performance_reviews.updated_at');

        if (in_array($status, ['draft', 'self_submitted', 'manager_finalized'], true)) {
            $query->where('annual_performance_reviews.status', $status);
        }

        $reviews = $query->get();
        foreach ($reviews as $review) {
            $this->ensureAnnualWorkflowStep($review, $settings);
        }
        $queueSnapshot = $reviews
            ->groupBy(fn ($review) => (string) ($review->workflow_step ?? 'self_draft'))
            ->map(fn ($items) => $items->count())
            ->sortKeys();

        $lastWorkflowEventsByReviewId = AnnualReviewWorkflowEvent::query()
            ->whereIn('annual_performance_review_id', $reviews->pluck('id')->all())
            ->with('actor:user_id,name,role_name')
            ->orderByDesc('created_at')
            ->get()
            ->groupBy('annual_performance_review_id')
            ->map(fn ($events) => $events->first());

        $activityActionOptions = $lastWorkflowEventsByReviewId
            ->pluck('action')
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();

        if ($activityAction !== '' && $activityAction !== 'all') {
            $reviews = $reviews->filter(function ($review) use ($lastWorkflowEventsByReviewId, $activityAction) {
                $event = $lastWorkflowEventsByReviewId->get($review->id);
                return ($event?->action ?? null) === $activityAction;
            })->values();
        }

        if ($workflowStepFilter !== '' && $workflowStepFilter !== 'all') {
            $reviews = $reviews->filter(fn ($review) => (string) ($review->workflow_step ?? 'self_draft') === $workflowStepFilter)->values();
        }

        return view('performance.team-annual-reviews', [
            'reviews' => $reviews,
            'status' => $status,
            'year' => $year,
            'years' => range(now()->year - 2, now()->year + 1),
            'settings' => $settings,
            'workflowSteps' => $this->annualWorkflowSequence($settings),
            'lastWorkflowEventsByReviewId' => $lastWorkflowEventsByReviewId,
            'activityAction' => $activityAction,
            'activityActionOptions' => $activityActionOptions,
            'workflowStepFilter' => $workflowStepFilter,
            'queueSnapshot' => $queueSnapshot,
        ]);
    }

    public function generateAnnualReviews(Request $request)
    {
        $user = Auth::user();
        abort_unless($user !== null, 403);
        abort_unless($this->canManageTeamPerformance($user), 403);

        $validated = $request->validate([
            'year' => 'required|integer|min:2000|max:2100',
        ]);

        $year = (int) $validated['year'];
        $settings = PerformanceReviewSetting::current();
        $valuesCatalog = $settings->valuesCatalog();

        $activeEmployees = User::query()
            ->where('status', 'Active')
            ->orderBy('name')
            ->get(['user_id']);

        $created = 0;
        $alreadyExists = 0;

        DB::transaction(function () use (
            $activeEmployees,
            $year,
            $valuesCatalog,
            &$created,
            &$alreadyExists
        ) {
            foreach ($activeEmployees as $employee) {
                $review = AnnualPerformanceReview::query()->firstOrCreate(
                    ['user_id' => $employee->user_id, 'review_year' => $year],
                    ['status' => 'draft', 'workflow_step' => 'self_draft']
                );
                $this->ensureAnnualWorkflowStep($review, $settings);

                if ($review->wasRecentlyCreated) {
                    $created++;
                } else {
                    $alreadyExists++;
                }

                $objectives = $this->objectivesForYear((string) $employee->user_id, $year);
                foreach ($objectives as $objective) {
                    AnnualObjectiveRating::query()->firstOrCreate([
                        'annual_performance_review_id' => $review->id,
                        'performance_objective_id' => $objective->id,
                    ]);
                }

                foreach ($valuesCatalog as $label) {
                    $normalizedLabel = $this->normalizeCatalogLabel($label);
                    if ($normalizedLabel === null) {
                        continue;
                    }

                    $key = Str::of($normalizedLabel)->slug()->toString();
                    AnnualValueRating::query()->firstOrCreate([
                        'annual_performance_review_id' => $review->id,
                        'value_key' => $key,
                    ], [
                        'value_label' => $normalizedLabel,
                    ]);
                }
            }
        });

        Toastr::success(
            sprintf(
                'Annual reviews generated for %d active employees (%d new, %d existing).',
                $activeEmployees->count(),
                $created,
                $alreadyExists
            ),
            'Success'
        );

        return redirect()->route('performance/team/annual-reviews', ['year' => $year]);
    }

    public function managerAnnualReview(Request $request, int $reviewId)
    {
        $user = Auth::user();
        abort_unless($user !== null, 403);
        abort_unless($this->canManageTeamPerformance($user), 403);

        $settings = PerformanceReviewSetting::current();
        $review = AnnualPerformanceReview::query()->findOrFail($reviewId);
        $this->ensureAnnualWorkflowStep($review, $settings);
        $subjectUser = User::query()->where('user_id', $review->user_id)->firstOrFail();
        $managerCanEdit = ($review->workflow_step ?? '') === 'manager_draft';

        if ($request->isMethod('post')) {
            if (! $managerCanEdit) {
                Toastr::error('Manager appraisal is locked until employee submits self appraisal, or already finalized.', 'Error');
                return redirect()->back();
            }

            $validated = $request->validate([
                'manager_summary' => 'nullable|string|max:6000',
                'objective_manager_rating' => 'nullable|array',
                'objective_manager_rating.*' => 'nullable|integer|min:1|max:5',
                'objective_manager_comment' => 'nullable|array',
                'objective_manager_comment.*' => 'nullable|string|max:3000',
                'value_manager_rating' => 'nullable|array',
                'value_manager_rating.*' => 'nullable|integer|min:1|max:5',
                'value_manager_comment' => 'nullable|array',
                'value_manager_comment.*' => 'nullable|string|max:3000',
                'finalize' => 'nullable|string|in:0,1',
            ]);

            DB::transaction(function () use ($validated, $review, $settings, $user) {
                $review->manager_summary = $validated['manager_summary'] ?? null;
                $review->manager_user_id = $user->user_id;

                $objectiveRatings = $validated['objective_manager_rating'] ?? [];
                $objectiveComments = $validated['objective_manager_comment'] ?? [];
                foreach ($objectiveRatings as $ratingId => $ratingValue) {
                    $row = AnnualObjectiveRating::query()
                        ->where('annual_performance_review_id', $review->id)
                        ->where('id', $ratingId)
                        ->first();
                    if (! $row) {
                        continue;
                    }
                    $row->manager_rating = $ratingValue !== null && $ratingValue !== '' ? (int) $ratingValue : null;
                    $row->manager_comment = $objectiveComments[$ratingId] ?? null;
                    $row->save();
                }

                $valueRatings = $validated['value_manager_rating'] ?? [];
                $valueComments = $validated['value_manager_comment'] ?? [];
                foreach ($valueRatings as $ratingId => $ratingValue) {
                    $row = AnnualValueRating::query()
                        ->where('annual_performance_review_id', $review->id)
                        ->where('id', $ratingId)
                        ->first();
                    if (! $row) {
                        continue;
                    }
                    $row->manager_rating = $ratingValue !== null && $ratingValue !== '' ? (int) $ratingValue : null;
                    $row->manager_comment = $valueComments[$ratingId] ?? null;
                    $row->save();
                }

                $review->manager_objectives_score = $this->computeAverageScore(
                    AnnualObjectiveRating::query()
                        ->where('annual_performance_review_id', $review->id)
                        ->pluck('manager_rating')
                        ->all()
                );
                $review->manager_values_score = $settings->annual_section_values_enabled
                    ? $this->computeAverageScore(
                        AnnualValueRating::query()
                            ->where('annual_performance_review_id', $review->id)
                            ->pluck('manager_rating')
                            ->all()
                    )
                    : null;

                $objectiveWeight = max(0, min(100, (int) $settings->objective_weight));
                $valuesWeight = max(0, min(100, (int) $settings->values_weight));

                $objectiveComponent = $review->manager_objectives_score !== null
                    ? $review->manager_objectives_score * ($objectiveWeight / 100)
                    : 0;
                $valuesComponent = $review->manager_values_score !== null
                    ? $review->manager_values_score * ($valuesWeight / 100)
                    : 0;
                $review->manager_final_score = round($objectiveComponent + $valuesComponent, 2);

                if (($validated['finalize'] ?? '0') === '1') {
                    $review->manager_submitted_at = now();
                    $review->status = 'self_submitted';
                    $this->transitionAnnualWorkflow(
                        $review,
                        $this->nextAnnualWorkflowStepAfterManager($settings),
                        'manager_submitted',
                        $user,
                        'Manager completed objective/value appraisal.',
                        true,
                        route('performance/annual/review', ['year' => $review->review_year])
                    );
                } elseif ($review->status === 'draft') {
                    $review->status = 'self_submitted';
                }

                $review->save();
            });

            Toastr::success(($validated['finalize'] ?? '0') === '1' ? 'Annual appraisal finalized.' : 'Manager appraisal saved.', 'Success');
            return redirect()->back();
        }

        $monthlyGoals = PerformanceGoalEntry::query()
            ->where('user_id', $review->user_id)
            ->where('period_type', 'monthly')
            ->where('period_year', $review->review_year)
            ->orderBy('period_number')
            ->get();

        $objectiveRatings = AnnualObjectiveRating::query()
            ->where('annual_performance_review_id', $review->id)
            ->with('objective')
            ->get();

        $valueRatings = AnnualValueRating::query()
            ->where('annual_performance_review_id', $review->id)
            ->orderBy('value_label')
            ->get();
        $workflowEvents = AnnualReviewWorkflowEvent::query()
            ->where('annual_performance_review_id', $review->id)
            ->with('actor:user_id,name,role_name')
            ->latest()
            ->limit(25)
            ->get();

        return view('performance.annual-review', [
            'review' => $review,
            'settings' => $settings,
            'monthlyGoals' => $monthlyGoals,
            'objectiveRatings' => $objectiveRatings,
            'valueRatings' => $valueRatings,
            'workflowEvents' => $workflowEvents,
            'year' => $review->review_year,
            'years' => range(now()->year - 2, now()->year + 1),
            'mode' => 'manager',
            'subjectUser' => $subjectUser,
            'subjectName' => $subjectUser->name,
            'managerCanEdit' => $managerCanEdit,
        ]);
    }

    public function downloadAnnualReview(int $year)
    {
        $user = Auth::user();
        abort_unless($user !== null, 403);

        $review = AnnualPerformanceReview::query()
            ->where('user_id', $user->user_id)
            ->where('review_year', $year)
            ->firstOrFail();

        return $this->downloadAnnualReviewPdfFor($review, $user);
    }

    public function downloadAnnualReviewById(int $id)
    {
        $user = Auth::user();
        abort_unless($user !== null, 403);
        abort_unless($this->canManageTeamPerformance($user), 403);

        $review = AnnualPerformanceReview::query()->findOrFail($id);
        $subjectUser = User::query()->where('user_id', $review->user_id)->firstOrFail();

        return $this->downloadAnnualReviewPdfFor($review, $subjectUser);
    }

    public function acknowledgeAnnualReview(int $year)
    {
        $user = Auth::user();
        abort_unless($user !== null, 403);

        $settings = PerformanceReviewSetting::current();
        $review = AnnualPerformanceReview::query()
            ->where('user_id', $user->user_id)
            ->where('review_year', $year)
            ->firstOrFail();

        $this->ensureAnnualWorkflowStep($review, $settings);

        if ($review->workflow_step !== 'employee_ack_pending') {
            Toastr::error('Employee acknowledgment is not available at the current stage.', 'Error');
            return redirect()->back();
        }

        $review->employee_acknowledged_at = now();
        $this->transitionAnnualWorkflow(
            $review,
            'finalized',
            'employee_acknowledged',
            $user,
            'Employee acknowledged annual appraisal.',
            true,
            route('performance/team/annual-reviews', ['year' => $review->review_year])
        );
        $review->save();

        Toastr::success('Annual appraisal acknowledged and finalized.', 'Success');
        return redirect()->back();
    }

    public function adminProgressAnnualReview(Request $request, int $reviewId)
    {
        $user = Auth::user();
        abort_unless($user !== null && $user->isAdmin(), 403);

        $settings = PerformanceReviewSetting::current();
        abort_unless((bool) $settings->annual_allow_admin_manual_progress, 403);

        $validated = $request->validate([
            'target_step' => 'required|string|max:100',
            'workflow_notes' => 'nullable|string|max:2000',
        ]);

        $review = AnnualPerformanceReview::query()->findOrFail($reviewId);
        $this->ensureAnnualWorkflowStep($review, $settings);

        $allowed = $this->annualWorkflowSequence($settings);
        $target = trim($validated['target_step']);
        if (in_array(strtolower($target), ['complete', 'completed'], true)) {
            $target = 'finalized';
        }
        if ($target === 'next') {
            $target = $this->nextStepFromCurrent($review->workflow_step, $allowed);
        }
        abort_unless(in_array($target, $allowed, true), 422);

        $currentIndex = array_search($review->workflow_step, $allowed, true);
        $targetIndex = array_search($target, $allowed, true);
        abort_unless($currentIndex !== false && $targetIndex !== false && $targetIndex >= $currentIndex, 422);

        if ($review->workflow_step === 'calibration_pending' && $target !== 'calibration_pending' && $review->calibration_completed_at === null) {
            $review->calibration_completed_at = now();
        }
        if ($review->workflow_step === 'joint_review_pending' && $target !== 'joint_review_pending' && $review->joint_review_at === null) {
            $review->joint_review_at = now();
        }
        if ($review->workflow_step === 'employee_ack_pending' && $target !== 'employee_ack_pending' && $review->employee_acknowledged_at === null) {
            $review->employee_acknowledged_at = now();
        }
        if ($review->workflow_step === 'manager_draft' && $targetIndex > $currentIndex && $review->manager_submitted_at === null) {
            $review->manager_submitted_at = now();
        }

        $this->transitionAnnualWorkflow(
            $review,
            $target,
            'admin_progressed',
            $user,
            trim((string) ($validated['workflow_notes'] ?? '')) ?: 'Manual progression by admin.',
            true,
            route('performance/annual/review', ['year' => $review->review_year])
        );
        $review->save();

        Toastr::success('Annual review workflow progressed to ' . str_replace('_', ' ', $target) . '.', 'Success');
        return redirect()->back();
    }

    private function objectivesForYear(string $userId, int $year)
    {
        return PerformanceObjective::query()
            ->where('user_id', $userId)
            ->whereYear('period_start', '<=', $year)
            ->whereYear('period_end', '>=', $year)
            ->whereIn('period_type', ['quarterly', 'biannual', 'annual'])
            ->orderBy('period_start')
            ->get();
    }

    private function computeAverageScore(array $scores): ?float
    {
        $filtered = array_values(array_filter($scores, static fn ($score) => $score !== null));
        if ($filtered === []) {
            return null;
        }

        return round(array_sum($filtered) / count($filtered), 2);
    }

    private function downloadAnnualReviewPdfFor(AnnualPerformanceReview $review, User $subjectUser)
    {
        $settings = PerformanceReviewSetting::current();
        $monthlyGoals = PerformanceGoalEntry::query()
            ->where('user_id', $review->user_id)
            ->where('period_type', 'monthly')
            ->where('period_year', $review->review_year)
            ->orderBy('period_number')
            ->get();

        $objectiveRatings = AnnualObjectiveRating::query()
            ->where('annual_performance_review_id', $review->id)
            ->with('objective')
            ->get();

        $valueRatings = AnnualValueRating::query()
            ->where('annual_performance_review_id', $review->id)
            ->orderBy('value_label')
            ->get();

        $fileName = sprintf(
            'Annual-Appraisal-%s-%d.pdf',
            preg_replace('/[^A-Za-z0-9_\-]/', '_', (string) $subjectUser->name) ?: $subjectUser->user_id,
            (int) $review->review_year
        );

        return Pdf::loadView('performance.annual-review-pdf', [
            'review' => $review,
            'subjectUser' => $subjectUser,
            'settings' => $settings,
            'monthlyGoals' => $monthlyGoals,
            'objectiveRatings' => $objectiveRatings,
            'valueRatings' => $valueRatings,
        ])->setPaper('a4')
            ->download($fileName);
    }

    private function canManageTeamPerformance($user): bool
    {
        return $user->isAdmin() || $user->hasRole(['HR Manager', 'Operations Manager']);
    }

    private function normalizeCatalogLabel($label): ?string
    {
        if (is_string($label)) {
            $value = trim($label);
            return $value !== '' ? $value : null;
        }

        if (is_array($label)) {
            $candidate = $label['label'] ?? $label['name'] ?? $label['title'] ?? $label['value'] ?? null;
            if (is_string($candidate)) {
                $value = trim($candidate);
                return $value !== '' ? $value : null;
            }
        }

        return null;
    }

    private function annualWorkflowSequence(PerformanceReviewSetting $settings): array
    {
        $sequence = ['self_draft'];

        if ((bool) $settings->annual_stage_manager_submit_required) {
            $sequence[] = 'manager_draft';
        }

        if ((bool) $settings->annual_stage_calibration_enabled) {
            $sequence[] = 'calibration_pending';
        }

        if ((bool) $settings->annual_stage_joint_review_enabled) {
            $sequence[] = 'joint_review_pending';
        }

        if ((bool) $settings->annual_stage_employee_ack_required) {
            $sequence[] = 'employee_ack_pending';
        }

        $sequence[] = 'finalized';

        return $sequence;
    }

    private function ensureAnnualWorkflowStep(AnnualPerformanceReview $review, PerformanceReviewSetting $settings): void
    {
        $allowed = $this->annualWorkflowSequence($settings);
        if (is_string($review->workflow_step) && in_array($review->workflow_step, $allowed, true)) {
            return;
        }

        if ($review->status === 'manager_finalized') {
            $review->workflow_step = 'finalized';
        } elseif ($review->status === 'self_submitted') {
            $review->workflow_step = (bool) $settings->annual_stage_manager_submit_required
                ? 'manager_draft'
                : $this->nextAnnualWorkflowStepAfterManager($settings);
        } else {
            $review->workflow_step = 'self_draft';
        }

        $review->save();
    }

    private function nextAnnualWorkflowStepAfterManager(PerformanceReviewSetting $settings): string
    {
        $sequence = $this->annualWorkflowSequence($settings);
        $from = (bool) $settings->annual_stage_manager_submit_required ? 'manager_draft' : 'self_draft';

        return $this->nextStepFromCurrent($from, $sequence);
    }

    private function nextStepFromCurrent(string $current, array $sequence): string
    {
        $currentIndex = array_search($current, $sequence, true);
        if ($currentIndex === false) {
            return 'finalized';
        }

        return $sequence[$currentIndex + 1] ?? 'finalized';
    }

    private function transitionAnnualWorkflow(
        AnnualPerformanceReview $review,
        string $targetStep,
        string $action,
        User $actor,
        ?string $note = null,
        bool $notify = true,
        ?string $targetUrl = null
    ): void {
        $fromStep = (string) ($review->workflow_step ?? 'self_draft');
        $toStep = trim($targetStep) !== '' ? trim($targetStep) : $fromStep;

        $review->workflow_step = $toStep;

        if ($toStep === 'finalized') {
            $review->status = 'manager_finalized';
            $review->finalized_at = $review->finalized_at ?: now();
            $review->finalized_by_user_id = (string) $actor->user_id;
        } elseif ($review->status === 'manager_finalized') {
            $review->status = 'self_submitted';
            $review->finalized_at = null;
            $review->finalized_by_user_id = null;
        }

        if ($note !== null && trim($note) !== '') {
            $line = '[' . now()->format('Y-m-d H:i') . ' ' . $actor->user_id . '] ' . trim($note);
            $review->workflow_notes = trim((string) ($review->workflow_notes ? ($review->workflow_notes . PHP_EOL . $line) : $line));
        }

        $notifiedEmails = [];
        if ($notify && $fromStep !== $toStep) {
            $notifiedEmails = $this->notifyAnnualWorkflowTransition($review, $fromStep, $toStep, $action, $actor, $targetUrl);
        }

        if ($fromStep !== $toStep) {
            AnnualReviewWorkflowEvent::query()->create([
                'annual_performance_review_id' => $review->id,
                'from_step' => $fromStep,
                'to_step' => $toStep,
                'action' => $action,
                'actor_user_id' => $actor->user_id,
                'actor_role' => $actor->role_name,
                'notes' => $note,
                'notified_emails' => $notifiedEmails !== [] ? array_values($notifiedEmails) : null,
            ]);
        }
    }

    private function notifyAnnualWorkflowTransition(
        AnnualPerformanceReview $review,
        string $fromStep,
        string $toStep,
        string $action,
        User $actor,
        ?string $targetUrl = null
    ): array {
        try {
            MailSettingsManager::apply(CompanySettings::current());

            $employee = User::query()->where('user_id', $review->user_id)->first();
            $manager = $review->manager_user_id
                ? User::query()->where('user_id', $review->manager_user_id)->first()
                : null;

            $emails = [];
            $subject = 'Annual appraisal workflow update';

            if ($action === 'self_submitted') {
                $subject = 'Employee self appraisal submitted';
                $emails = User::query()
                    ->whereIn('role_name', ['Super Admin', 'Admin', 'HR Manager', 'Operations Manager'])
                    ->where('status', 'Active')
                    ->pluck('email')
                    ->filter()
                    ->values()
                    ->all();
            } elseif ($action === 'manager_submitted' || $toStep === 'employee_ack_pending') {
                $subject = 'Manager appraisal completed - employee acknowledgement required';
                if ($employee && filled($employee->email)) {
                    $emails[] = $employee->email;
                }
            } elseif ($action === 'employee_acknowledged' || $toStep === 'finalized') {
                $subject = 'Annual appraisal finalized';
                if ($employee && filled($employee->email)) {
                    $emails[] = $employee->email;
                }
                if ($manager && filled($manager->email)) {
                    $emails[] = $manager->email;
                }
            } elseif ($action === 'admin_progressed') {
                $subject = 'Annual appraisal workflow manually progressed by admin';
                if ($employee && filled($employee->email)) {
                    $emails[] = $employee->email;
                }
                if ($manager && filled($manager->email)) {
                    $emails[] = $manager->email;
                }
            }

            $emails = collect($emails)->filter()->map(fn ($email) => strtolower(trim((string) $email)))->unique()->values()->all();
            if ($emails === []) {
                return [];
            }

            $employeeName = $employee?->name ?: $review->user_id;
            $workflowLink = $targetUrl ?: route('performance/annual/review', ['year' => $review->review_year]);
            $messageBody = implode("\n", [
                'Annual appraisal workflow update',
                'Employee: ' . $employeeName,
                'Year: ' . (string) $review->review_year,
                'From: ' . str_replace('_', ' ', $fromStep),
                'To: ' . str_replace('_', ' ', $toStep),
                'Action: ' . str_replace('_', ' ', $action),
                'By: ' . ($actor->name ?: $actor->user_id) . ' (' . ($actor->role_name ?: 'N/A') . ')',
                'Open: ' . $workflowLink,
            ]);

            foreach ($emails as $recipient) {
                Mail::raw($messageBody, function ($message) use ($recipient, $subject): void {
                    $message->to($recipient)->subject($subject);
                });
            }

            return $emails;
        } catch (\Throwable) {
            return [];
        }
    }
}
