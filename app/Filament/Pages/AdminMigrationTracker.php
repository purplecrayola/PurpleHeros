<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class AdminMigrationTracker extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';
    protected static ?string $navigationGroup = 'Administration';
    protected static ?int $navigationSort = 10;
    protected static ?string $title = 'Admin Migration Tracker';
    protected static ?string $slug = 'admin-migration-tracker';

    protected static string $view = 'filament.pages.admin-migration-tracker';

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user && $user->canManageSettings();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public function getRows(): array
    {
        return (array) config('admin_migration_tracker.inventory', []);
    }

    public function getSummary(): array
    {
        $rows = collect($this->getRows());

        return [
            'total' => $rows->count(),
            'cutover_live' => $rows->where('status', 'cutover_live')->count(),
            'in_progress' => $rows->where('status', 'in_progress')->count(),
            'planned' => $rows->where('status', 'planned')->count(),
            'no_equivalent' => $rows->where('status', 'no_equivalent')->count(),
        ];
    }
}
