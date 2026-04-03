<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\Login as BaseLogin;
use Filament\Support\Enums\MaxWidth;

class Login extends BaseLogin
{
    protected static string $view = 'filament.auth.login';

    public function getMaxWidth(): MaxWidth|string|null
    {
        return MaxWidth::ScreenTwoExtraLarge;
    }

    public function hasLogo(): bool
    {
        return false;
    }

    public function getHeading(): string
    {
        return '';
    }
}

