<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class PayrollDefaults extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Administration';
    protected static ?int $navigationSort = 40;
    protected static ?string $title = 'Payroll Defaults';
    protected static ?string $slug = 'payroll-defaults';

    protected static string $view = 'filament.pages.payroll-defaults';

    public static function canAccess(): bool
    {
        $user = auth()->user();

        return $user && $user->canManageSettings();
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }
}
