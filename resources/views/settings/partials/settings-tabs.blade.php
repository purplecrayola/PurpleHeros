@php
    $active = $active ?? '';
@endphp

<div class="settings-tab-groups mb-4">
    @if(Auth::user()->isAdmin())
        <div class="settings-tab-group">
            <div class="settings-tab-group__label">Organization</div>
            <div class="nav nav-pills settings-tab-nav" role="tablist">
                <a class="nav-link {{ $active === 'company' ? 'active' : '' }}" href="{{ url('/admin/company-settings') }}">
                    <i class="la la-building mr-1"></i> Company Profile
                </a>
                <a class="nav-link {{ $active === 'localization' ? 'active' : '' }}" href="{{ url('/admin/company-settings') }}">
                    <i class="la la-clock-o mr-1"></i> Localization
                </a>
            </div>
        </div>

        <div class="settings-tab-group">
            <div class="settings-tab-group__label">Payroll & Compliance</div>
            <div class="nav nav-pills settings-tab-nav" role="tablist">
                <a class="nav-link {{ $active === 'payroll' ? 'active' : '' }}" href="{{ url('/admin/payroll-defaults') }}">
                    <i class="la la-money mr-1"></i> Payroll Defaults
                </a>
            </div>
        </div>

        <div class="settings-tab-group">
            <div class="settings-tab-group__label">Communication</div>
            <div class="nav nav-pills settings-tab-nav" role="tablist">
                <a class="nav-link {{ $active === 'email' ? 'active' : '' }}" href="{{ url('/admin/email-settings') }}">
                    <i class="la la-at mr-1"></i> Email Delivery
                </a>
            </div>
        </div>

        <div class="settings-tab-group">
            <div class="settings-tab-group__label">Access & Security</div>
            <div class="nav nav-pills settings-tab-nav" role="tablist">
                <a class="nav-link {{ $active === 'roles' ? 'active' : '' }}" href="{{ url('/admin/roles-permissions') }}">
                    <i class="la la-key mr-1"></i> Roles & Permissions
                </a>
                <a class="nav-link {{ $active === 'password' ? 'active' : '' }}" href="{{ route('change/password') }}">
                    <i class="la la-lock mr-1"></i> Change Password
                </a>
            </div>
        </div>
    @else
        <div class="settings-tab-group">
            <div class="settings-tab-group__label">Account</div>
            <div class="nav nav-pills settings-tab-nav" role="tablist">
                <a class="nav-link {{ $active === 'profile' ? 'active' : '' }}" href="{{ route('profile_user') }}">
                    <i class="la la-user mr-1"></i> My Profile
                </a>
            </div>
        </div>

        <div class="settings-tab-group">
            <div class="settings-tab-group__label">Security</div>
            <div class="nav nav-pills settings-tab-nav" role="tablist">
                <a class="nav-link {{ $active === 'password' ? 'active' : '' }}" href="{{ route('change/password') }}">
                    <i class="la la-lock mr-1"></i> Change Password
                </a>
            </div>
        </div>
    @endif
</div>
