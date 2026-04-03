<?php

namespace App\Http\Controllers;

use App\Models\LearningAsset;
use App\Models\LearningBookmark;
use App\Models\LearningCourse;
use App\Models\LearningEnrollment;
use App\Models\LearningProgressEvent;
use App\Models\ProfileInformation;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LearningCatalogController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        abort_unless($user !== null, 403);

        $query = LearningCourse::query()
            ->where('status', 'active')
            ->where('visibility_status', 'published')
            ->where('catalog_visible', true);

        $this->applyAudienceFilterForUser($query, $user);

        $query
            ->withCount(['assets', 'enrollments']);

        if (filled($request->get('q'))) {
            $q = trim((string) $request->get('q'));
            $query->where(function ($inner) use ($q): void {
                $inner->where('title', 'like', "%{$q}%")
                    ->orWhere('course_code', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%");
            });
        }

        $courses = $query->orderBy('title')->paginate(12)->withQueryString();

        $myEnrollments = LearningEnrollment::query()
            ->where('user_id', $user->user_id)
            ->with('course')
            ->orderByDesc('updated_at')
            ->limit(30)
            ->get()
            ->keyBy('learning_course_id');

        return view('learning.catalog', [
            'courses' => $courses,
            'myEnrollments' => $myEnrollments,
        ]);
    }

    public function viewCourse(int $courseId)
    {
        $user = Auth::user();
        abort_unless($user !== null, 403);

        $course = LearningCourse::query()
            ->where('id', $courseId)
            ->where('status', 'active')
            ->where('visibility_status', 'published')
            ->where('catalog_visible', true)
            ->with(['assets' => fn ($q) => $q->where('status', 'active')->orderBy('sort_order')])
            ->firstOrFail();

        abort_unless($this->canUserAccessCourse($user, $course), 403);

        $enrollment = LearningEnrollment::query()
            ->where('learning_course_id', $course->id)
            ->where('user_id', $user->user_id)
            ->first();

        if (! $enrollment) {
            Toastr::error('You are not enrolled in this course yet.', 'Error');
            return redirect()->route('learning/catalog');
        }

        $bookmarks = LearningBookmark::query()
            ->where('learning_enrollment_id', $enrollment->id)
            ->latest()
            ->limit(50)
            ->get()
            ->groupBy('learning_asset_id');

        $assetProgress = LearningProgressEvent::query()
            ->where('learning_enrollment_id', $enrollment->id)
            ->whereNotNull('learning_asset_id')
            ->selectRaw('learning_asset_id, MAX(progress_percent) as max_progress')
            ->groupBy('learning_asset_id')
            ->pluck('max_progress', 'learning_asset_id');

        return view('learning.course', [
            'course' => $course,
            'enrollment' => $enrollment,
            'bookmarks' => $bookmarks,
            'assetProgress' => $assetProgress,
        ]);
    }

    private function applyAudienceFilterForUser(Builder $query, $user): void
    {
        $department = trim((string) ($user->department ?? ''));
        $roleName = trim((string) ($user->role_name ?? ''));
        $locationValues = $this->resolveUserLocations((string) $user->user_id);

        $query->where(function (Builder $audienceQuery) use ($department, $roleName, $locationValues): void {
            $audienceQuery->whereNull('audience_scope')
                ->orWhere('audience_scope', 'all')
                ->orWhere(function (Builder $filteredQuery) use ($department, $roleName, $locationValues): void {
                    $filteredQuery->where('audience_scope', 'filtered')
                        ->where(function (Builder $matchQuery) use ($department, $roleName, $locationValues): void {
                            $hasAnyMatchSignal = false;
                            if ($department !== '') {
                                $hasAnyMatchSignal = true;
                                $matchQuery->orWhereJsonContains('target_departments', $department);
                            }

                            if ($roleName !== '') {
                                $hasAnyMatchSignal = true;
                                $matchQuery->orWhereJsonContains('target_roles', $roleName);
                            }

                            foreach ($locationValues as $location) {
                                $hasAnyMatchSignal = true;
                                $matchQuery->orWhereJsonContains('target_locations', $location);
                            }

                            if (! $hasAnyMatchSignal) {
                                $matchQuery->whereRaw('1 = 0');
                            }
                        });
                });
        });
    }

    private function canUserAccessCourse($user, LearningCourse $course): bool
    {
        if (($course->audience_scope ?? 'all') !== 'filtered') {
            return true;
        }

        $department = trim((string) ($user->department ?? ''));
        $roleName = trim((string) ($user->role_name ?? ''));
        $locations = $this->resolveUserLocations((string) $user->user_id);

        $targetDepartments = collect($course->target_departments ?? [])->filter()->values();
        $targetRoles = collect($course->target_roles ?? [])->filter()->values();
        $targetLocations = collect($course->target_locations ?? [])->filter()->values();

        if ($department !== '' && $targetDepartments->contains($department)) {
            return true;
        }
        if ($roleName !== '' && $targetRoles->contains($roleName)) {
            return true;
        }
        foreach ($locations as $location) {
            if ($targetLocations->contains($location)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<int, string>
     */
    private function resolveUserLocations(string $userId): array
    {
        if ($userId === '') {
            return [];
        }

        $profile = ProfileInformation::query()->where('user_id', $userId)->first();
        if (! $profile) {
            return [];
        }

        $items = [];
        $state = trim((string) ($profile->state ?? ''));
        $country = trim((string) ($profile->country ?? ''));
        if ($state !== '') {
            $items[] = $state;
        }
        if ($country !== '') {
            $items[] = $country;
        }

        return array_values(array_unique($items));
    }

    public function startCourse(int $courseId): RedirectResponse
    {
        $user = Auth::user();
        abort_unless($user !== null, 403);

        $course = LearningCourse::query()->where('id', $courseId)->where('status', 'active')->firstOrFail();

        $enrollment = LearningEnrollment::query()
            ->where('learning_course_id', $course->id)
            ->where('user_id', $user->user_id)
            ->first();

        if (! $enrollment) {
            Toastr::error('You are not enrolled in this course yet.', 'Error');
            return redirect()->route('learning/catalog');
        }

        DB::transaction(function () use ($enrollment, $user): void {
            if (! $enrollment->started_at) {
                $enrollment->started_at = now();
            }
            $enrollment->last_activity_at = now();
            if ($enrollment->status === 'not_started') {
                $enrollment->status = 'in_progress';
            }
            $enrollment->save();

            LearningProgressEvent::query()->create([
                'learning_enrollment_id' => $enrollment->id,
                'event_type' => 'start',
                'progress_percent' => $enrollment->completion_percent,
                'created_by_user_id' => $user->user_id,
            ]);
        });

        if ($course->delivery_mode === 'virtual' && filled($course->join_link)) {
            return redirect()->away((string) $course->join_link);
        }

        return redirect()->route('learning/course/view', ['id' => $course->id]);
    }

    public function recordProgress(Request $request, int $enrollmentId): RedirectResponse
    {
        $user = Auth::user();
        abort_unless($user !== null, 403);

        $enrollment = LearningEnrollment::query()->findOrFail($enrollmentId);
        abort_unless($enrollment->user_id === $user->user_id, 403);

        $validated = $request->validate([
            'learning_asset_id' => 'nullable|integer|exists:learning_assets,id',
            'event_type' => 'required|string|max:40',
            'progress_percent' => 'nullable|numeric|min:0|max:100',
            'current_page' => 'nullable|integer|min:1',
            'total_pages' => 'nullable|integer|min:1',
            'position_seconds' => 'nullable|integer|min:0',
            'duration_seconds' => 'nullable|integer|min:1',
        ]);

        LearningProgressEvent::query()->create([
            'learning_enrollment_id' => $enrollment->id,
            'learning_asset_id' => $validated['learning_asset_id'] ?? null,
            'event_type' => $validated['event_type'],
            'progress_percent' => $validated['progress_percent'] ?? null,
            'current_page' => $validated['current_page'] ?? null,
            'total_pages' => $validated['total_pages'] ?? null,
            'position_seconds' => $validated['position_seconds'] ?? null,
            'duration_seconds' => $validated['duration_seconds'] ?? null,
            'created_by_user_id' => $user->user_id,
        ]);

        $this->recomputeEnrollmentCompletion($enrollment);

        Toastr::success('Progress recorded.', 'Success');
        return redirect()->back();
    }

    public function addBookmark(Request $request, int $enrollmentId): RedirectResponse
    {
        $user = Auth::user();
        abort_unless($user !== null, 403);

        $enrollment = LearningEnrollment::query()->findOrFail($enrollmentId);
        abort_unless($enrollment->user_id === $user->user_id, 403);

        $validated = $request->validate([
            'learning_asset_id' => 'nullable|integer|exists:learning_assets,id',
            'page_number' => 'nullable|integer|min:1',
            'position_seconds' => 'nullable|integer|min:0',
            'label' => 'nullable|string|max:255',
            'note' => 'nullable|string|max:2000',
        ]);

        LearningBookmark::query()->create([
            'learning_enrollment_id' => $enrollment->id,
            'learning_asset_id' => $validated['learning_asset_id'] ?? null,
            'page_number' => $validated['page_number'] ?? null,
            'position_seconds' => $validated['position_seconds'] ?? null,
            'label' => $validated['label'] ?? null,
            'note' => $validated['note'] ?? null,
            'created_by_user_id' => $user->user_id,
        ]);

        Toastr::success('Bookmark saved.', 'Success');
        return redirect()->back();
    }

    public function telemetry(Request $request, int $enrollmentId): JsonResponse
    {
        $user = Auth::user();
        abort_unless($user !== null, 403);

        $enrollment = LearningEnrollment::query()->findOrFail($enrollmentId);
        abort_unless($enrollment->user_id === $user->user_id, 403);

        $validated = $request->validate([
            'learning_asset_id' => 'required|integer|exists:learning_assets,id',
            'event_type' => 'required|string|in:view_page,listen,open_asset,complete',
            'progress_percent' => 'nullable|numeric|min:0|max:100',
            'current_page' => 'nullable|integer|min:1',
            'total_pages' => 'nullable|integer|min:1',
            'position_seconds' => 'nullable|integer|min:0',
            'duration_seconds' => 'nullable|integer|min:1',
        ]);

        $asset = LearningAsset::query()->findOrFail((int) $validated['learning_asset_id']);
        abort_unless((int) $asset->learning_course_id === (int) $enrollment->learning_course_id, 422);

        LearningProgressEvent::query()->create([
            'learning_enrollment_id' => $enrollment->id,
            'learning_asset_id' => $asset->id,
            'event_type' => $validated['event_type'],
            'progress_percent' => $validated['progress_percent'] ?? null,
            'current_page' => $validated['current_page'] ?? null,
            'total_pages' => $validated['total_pages'] ?? null,
            'position_seconds' => $validated['position_seconds'] ?? null,
            'duration_seconds' => $validated['duration_seconds'] ?? null,
            'created_by_user_id' => $user->user_id,
        ]);

        $this->recomputeEnrollmentCompletion($enrollment->fresh());

        return response()->json([
            'ok' => true,
            'completion_percent' => (float) $enrollment->fresh()->completion_percent,
            'status' => $enrollment->fresh()->status,
        ]);
    }

    private function recomputeEnrollmentCompletion(LearningEnrollment $enrollment): void
    {
        $assets = LearningAsset::query()
            ->where('learning_course_id', $enrollment->learning_course_id)
            ->where('status', 'active')
            ->get();

        $assetIds = $assets->pluck('id')->all();

        $completion = 0.0;
        if ($assetIds !== []) {
            $sum = 0.0;
            foreach ($assets as $asset) {
                $assetPercent = $this->computeAssetPercent($enrollment->id, $asset);
                $sum += $assetPercent;
            }
            $completion = round($sum / max(1, $assets->count()), 2);
        } else {
            $completion = (float) (LearningProgressEvent::query()
                ->where('learning_enrollment_id', $enrollment->id)
                ->max('progress_percent') ?? 0);
        }

        $enrollment->completion_percent = $completion;
        $enrollment->last_activity_at = now();
        if ($completion >= 100) {
            $enrollment->status = 'completed';
            $enrollment->completed_at = $enrollment->completed_at ?: now();
        } elseif ($completion > 0 && $enrollment->status === 'not_started') {
            $enrollment->status = 'in_progress';
        }
        $enrollment->save();
    }

    private function computeAssetPercent(int $enrollmentId, LearningAsset $asset): float
    {
        if ($asset->asset_type === 'pdf' && (int) ($asset->pages_count ?? 0) > 0) {
            $viewedCount = LearningProgressEvent::query()
                ->where('learning_enrollment_id', $enrollmentId)
                ->where('learning_asset_id', $asset->id)
                ->where('event_type', 'view_page')
                ->whereNotNull('current_page')
                ->distinct('current_page')
                ->count('current_page');

            return round(min(100, ($viewedCount / max(1, (int) $asset->pages_count)) * 100), 2);
        }

        if ($asset->asset_type === 'audio' && (int) ($asset->duration_seconds ?? 0) > 0) {
            $maxPosition = (int) (LearningProgressEvent::query()
                ->where('learning_enrollment_id', $enrollmentId)
                ->where('learning_asset_id', $asset->id)
                ->where('event_type', 'listen')
                ->max('position_seconds') ?? 0);

            return round(min(100, ($maxPosition / max(1, (int) $asset->duration_seconds)) * 100), 2);
        }

        return (float) (LearningProgressEvent::query()
            ->where('learning_enrollment_id', $enrollmentId)
            ->where('learning_asset_id', $asset->id)
            ->max('progress_percent') ?? 0);
    }
}
