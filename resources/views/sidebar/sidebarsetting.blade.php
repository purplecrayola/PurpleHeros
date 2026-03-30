<!-- Sidebar -->
@php($isAdmin = Auth::user()->isAdmin())
<div class="sidebar" id="sidebar">
    <div class="sidebar-inner slimscroll">
        <div class="sidebar-menu">
            <ul>
                <li><a href="{{ $isAdmin ? url('/admin') : route('em/dashboard') }}"><i class="la la-home"></i> <span>{{ $isAdmin ? 'Admin Console' : 'Back to Home' }}</span></a></li>
                @if($isAdmin)
                    <li class="{{ request()->is('admin/company-settings*') ? 'active' : '' }}"><a href="{{ url('/admin/company-settings') }}"><i class="la la-building"></i><span>Company Settings</span></a></li>
                    <li class="{{ request()->is('admin/payroll-defaults*') ? 'active' : '' }}"><a href="{{ url('/admin/payroll-defaults') }}"><i class="la la-money"></i><span>Payroll Defaults</span></a></li>
                    <li class="{{ request()->is('admin/performance-settings*') ? 'active' : '' }}"><a href="{{ url('/admin/performance-settings') }}"><i class="la la-line-chart"></i><span>Performance Settings</span></a></li>
                    <li class="{{ request()->is('admin/email-settings*') ? 'active' : '' }}"><a href="{{ url('/admin/email-settings') }}"><i class="la la-at"></i><span>Email Delivery</span></a></li>
                    <li class="{{ request()->is('admin/roles-permissions*') ? 'active' : '' }}"><a href="{{ url('/admin/roles-permissions') }}"><i class="la la-key"></i><span>Roles & Permissions</span></a></li>
                    <li class="{{ set_active(['change/password']) }}"><a href="{{ route('change/password') }}"><i class="la la-lock"></i><span>Change Password</span></a></li>
                @else
                    <li class="menu-title">Account</li>
                    <li class="{{ set_active(['profile_user']) }}"><a href="{{ route('profile_user') }}"><i class="la la-user"></i><span>My Profile</span></a></li>
                    <li class="{{ set_active(['change/password']) }}"><a href="{{ route('change/password') }}"><i class="la la-lock"></i><span>Change Password</span></a></li>
                @endif
            </ul>
        </div>
    </div>
</div>
<!-- Sidebar -->
