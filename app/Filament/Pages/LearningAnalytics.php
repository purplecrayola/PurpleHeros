<?php

namespace App\Filament\Pages;

use App\Models\LearningCourse;
use App\Models\LearningEnrollment;
use App\Models\LearningProgressEvent;
use App\Models\LearningAsset;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class LearningAnalytics extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';
    protected static ?string $navigationGroup = 'Learning';
    protected static ?int $navigationSort = 13;
    protected static ?string $title = 'Learning Analytics';
    protected static ?string $slug = 'learning-analytics';

    protected static string $view = 'filament.pages.learning-analytics';

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user && ($user->isAdmin() || $user->hasRole(['HR Manager', 'Operations Manager']));
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public function getSummary(): array
    {
        $enrollments = LearningEnrollment::query();

        return [
            'courses' => LearningCourse::query()->count(),
            'catalog_visible' => LearningCourse::query()->where('catalog_visible', true)->count(),
            'enrollments' => (clone $enrollments)->count(),
            'completed' => (clone $enrollments)->where('status', 'completed')->count(),
            'in_progress' => (clone $enrollments)->where('status', 'in_progress')->count(),
            'overdue' => (clone $enrollments)
                ->whereNotNull('due_at')
                ->where('due_at', '<', Carbon::now())
                ->where('status', '!=', 'completed')
                ->count(),
            'avg_completion' => (float) ((clone $enrollments)->avg('completion_percent') ?? 0),
            'progress_events' => LearningProgressEvent::query()->count(),
        ];
    }

    public function getTopCourses()
    {
        return LearningCourse::query()
            ->withCount('enrollments')
            ->withAvg('enrollments as avg_completion', 'completion_percent')
            ->orderByDesc('enrollments_count')
            ->limit(8)
            ->get();
    }

    public function getAssetTypeBreakdown()
    {
        $assetBase = LearningAsset::query()
            ->select('asset_type', DB::raw('COUNT(*) as assets_count'))
            ->groupBy('asset_type')
            ->pluck('assets_count', 'asset_type');

        $eventsByType = DB::table('learning_progress_events')
            ->join('learning_assets', 'learning_assets.id', '=', 'learning_progress_events.learning_asset_id')
            ->selectRaw('learning_assets.asset_type as asset_type, COUNT(learning_progress_events.id) as events_count')
            ->groupBy('learning_assets.asset_type')
            ->pluck('events_count', 'asset_type');

        $avgCompletionByType = DB::table('learning_progress_events')
            ->join('learning_assets', 'learning_assets.id', '=', 'learning_progress_events.learning_asset_id')
            ->selectRaw('learning_assets.asset_type as asset_type')
            ->selectRaw('AVG(learning_progress_events.progress_percent) as avg_completion')
            ->whereNotNull('learning_progress_events.progress_percent')
            ->groupBy('learning_assets.asset_type')
            ->pluck('avg_completion', 'asset_type');

        return $assetBase->keys()
            ->map(function (string $assetType) use ($assetBase, $eventsByType, $avgCompletionByType): array {
                return [
                    'asset_type' => $assetType,
                    'assets_count' => (int) ($assetBase[$assetType] ?? 0),
                    'events_count' => (int) ($eventsByType[$assetType] ?? 0),
                    'avg_completion' => round((float) ($avgCompletionByType[$assetType] ?? 0), 1),
                ];
            })
            ->sortBy('asset_type')
            ->values();
    }

    public function getOverdueEnrollments()
    {
        return LearningEnrollment::query()
            ->with(['course', 'user'])
            ->whereNotNull('due_at')
            ->where('due_at', '<', Carbon::now())
            ->where('status', '!=', 'completed')
            ->orderBy('due_at')
            ->limit(15)
            ->get();
    }
}
