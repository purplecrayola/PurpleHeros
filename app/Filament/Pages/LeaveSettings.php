<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class LeaveSettings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-adjustments-horizontal';
    protected static ?string $navigationGroup = 'Time & Attendance';
    protected static ?int $navigationSort = 60;
    protected static ?string $title = 'Leave Settings';
    protected static ?string $slug = 'leave-settings';

    protected static string $view = 'filament.pages.leave-settings';

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user && $user->canManageTimeAttendance();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }
}

