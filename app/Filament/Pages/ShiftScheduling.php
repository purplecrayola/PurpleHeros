<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class ShiftScheduling extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationGroup = 'Time & Attendance';
    protected static ?int $navigationSort = 70;
    protected static ?string $title = 'Shift Scheduling';
    protected static ?string $slug = 'shift-scheduling';

    protected static string $view = 'filament.pages.shift-scheduling';

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

