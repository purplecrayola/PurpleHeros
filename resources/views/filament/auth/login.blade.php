@php($brandSettings = \App\Models\CompanySettings::current())
@php($brandName = $brandSettings->login_brand_label ?: 'Purple Crayola')
@php($loginLogo = $brandSettings->assetUrl('login_logo_path', 'assets/img/brand/purplecrayola-black.svg'))
@php($loginImage = $brandSettings->assetUrl('login_image_path', 'assets/img/brand/pexels-jakubzerdzicki-28550000.jpg'))

<x-filament-panels::page.simple class="pc-admin-login-page">
    <div class="pc-admin-login-wrap">
        <section class="pc-admin-login-hero">
            <img src="{{ $loginImage }}" alt="{{ $brandName }}" class="pc-admin-login-hero-image" />
            <div class="pc-admin-login-hero-overlay"></div>
            <div class="pc-admin-login-hero-copy">
                <span class="pc-admin-login-kicker">{{ $brandName }}</span>
                <h1>{{ $brandName }}</h1>
                <p>{{ $brandSettings->login_hero_copy ?: 'Digital systems. Designed to work.' }}</p>
            </div>
        </section>

        <section class="pc-admin-login-panel">
            <div class="pc-admin-login-head">
                <div class="pc-admin-login-logo-tile">
                    <img src="{{ $loginLogo }}" alt="{{ $brandName }}">
                </div>
                <div>
                    <h2>Admin Sign In</h2>
                    <p>Secure control center access for payroll, people operations, and system configuration.</p>
                </div>
            </div>

            {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::AUTH_LOGIN_FORM_BEFORE, scopes: $this->getRenderHookScopes()) }}

            <x-filament-panels::form id="form" wire:submit="authenticate" class="pc-admin-login-form">
                {{ $this->form }}

                <x-filament-panels::form.actions
                    :actions="$this->getCachedFormActions()"
                    :full-width="$this->hasFullWidthFormActions()"
                />
            </x-filament-panels::form>

            {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::AUTH_LOGIN_FORM_AFTER, scopes: $this->getRenderHookScopes()) }}

            <div class="pc-admin-login-footnote">
                <span>{{ $brandSettings->login_help_line_two ?: $brandName }}</span>
                <span>{{ $brandSettings->login_help_line_three ?: 'Purple Crayola Employee Access' }}</span>
            </div>
        </section>
    </div>
</x-filament-panels::page.simple>

