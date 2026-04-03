<?php

namespace App\Providers\Filament;

use App\Models\CompanySettings;
use App\Filament\Pages\Auth\Login as AdminLogin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->brandName('Purple HR')
            ->favicon(fn (): string => CompanySettings::current()->assetUrl('favicon_path', 'assets/img/favicon.ico'))
            ->darkMode(false)
            ->login(AdminLogin::class)
            ->sidebarCollapsibleOnDesktop()
            ->colors([
                'primary' => Color::Indigo,
            ])
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): string => $this->themeLinkTag() . $this->brandTokenStyleTag()
            )
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                \App\Filament\Widgets\RoleWelcomePanel::class,
                \App\Filament\Widgets\HrSnapshotOverview::class,
                \App\Filament\Widgets\PayrollSnapshotOverview::class,
                \App\Filament\Widgets\AttendanceTrendChart::class,
                \App\Filament\Widgets\OvertimeStatusChart::class,
                \App\Filament\Widgets\LeaveVolumeChart::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }

    private function themeLinkTag(): string
    {
        $href = asset('css/filament/admin-theme.css');

        return "<link rel=\"stylesheet\" href=\"{$href}\">";
    }

    private function brandTokenStyleTag(): string
    {
        $settings = CompanySettings::current();

        $primary = $settings->color('brand_primary_color', '#8A00FF');
        $dark = $settings->color('brand_dark_color', '#00163F');
        $neutral = $settings->color('brand_neutral_color', '#E2E8F0');
        $sidebarText = $settings->color('sidebar_text_color', '#F5F7FF');
        $sidebarMuted = $settings->color('sidebar_muted_text_color', '#A9B8CC');

        return <<<HTML
<style>
    :root {
        --ax-primary: {$primary};
        --ax-dark: {$dark};
        --ax-neutral: {$neutral};
        --ax-sidebar-text: {$sidebarText};
        --ax-sidebar-muted: {$sidebarMuted};
    }
</style>
HTML;
    }
}
