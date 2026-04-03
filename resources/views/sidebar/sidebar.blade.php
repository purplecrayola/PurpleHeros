<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-inner slimscroll">
        <div id="sidebar-menu" class="sidebar-menu">
            @php($isAdmin = Auth::user()->isAdmin())
            <ul>
                <li class="menu-title"><span>Main</span></li>
                @if ($isAdmin)
                    <li class="{{ request()->is('admin*') ? 'active' : '' }}">
                        <a href="{{ url('/admin') }}">
                            <i class="la la-dashboard"></i>
                            <span>Admin Console</span>
                        </a>
                    </li>
                    <li class="{{ request()->is('admin/users*') ? 'active' : '' }}">
                        <a href="{{ url('/admin/users') }}">
                            <i class="la la-user-secret"></i>
                            <span>User Control</span>
                        </a>
                    </li>
                    <li class="{{ request()->is('admin/employees*') ? 'active' : '' }}">
                        <a href="{{ url('/admin/employees') }}">
                            <i class="la la-users"></i>
                            <span>Employees</span>
                        </a>
                    </li>
                    <li class="{{ request()->is('admin/staff-salaries*','admin/payslips*','admin/payroll-*') ? 'active' : '' }}">
                        <a href="{{ url('/admin/staff-salaries') }}">
                            <i class="la la-money"></i>
                            <span>Payroll</span>
                        </a>
                    </li>
                    <li class="{{ request()->is('admin/reports-hub*') ? 'active' : '' }}">
                        <a href="{{ url('/admin/reports-hub') }}">
                            <i class="la la-pie-chart"></i>
                            <span>Reports</span>
                        </a>
                    </li>
                    <li class="{{ request()->is('admin/performance-*') ? 'active' : '' }}">
                        <a href="{{ url('/admin/performance-hub') }}">
                            <i class="la la-line-chart"></i>
                            <span>Performance</span>
                        </a>
                    </li>
                    <li class="{{ request()->is('admin/company-settings*','admin/email-settings*','admin/roles-permissions*') ? 'active' : '' }}">
                        <a href="{{ url('/admin/company-settings') }}">
                            <i class="la la-cog"></i>
                            <span>Settings</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('em/dashboard') }}">
                            <i class="la la-id-badge"></i>
                            <span>Employee View</span>
                        </a>
                    </li>
                @else
                    <li>
                        <a class="{{ set_active(['em/dashboard']) }}" href="{{ route('em/dashboard') }}">
                            <i class="la la-dashboard"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>

                    <li class="menu-title"><span>Self Service</span></li>
                    <li class="{{ request()->is('profile_user','employee/profile/*') ? 'active' : '' }}">
                        <a href="{{ route('profile_user') }}">
                            <i class="la la-id-card"></i>
                            <span>My Profile</span>
                        </a>
                    </li>
                    <li class="{{ set_active(['form/leavesemployee/new']) }}">
                        <a href="{{ route('form/leavesemployee/new') }}">
                            <i class="la la-calendar"></i>
                            <span>My Leave</span>
                        </a>
                    </li>
                    <li class="{{ set_active(['attendance/employee/page']) }}">
                        <a href="{{ route('attendance/employee/page') }}">
                            <i class="la la-clock-o"></i>
                            <span>My Attendance</span>
                        </a>
                    </li>
                    <li class="{{ set_active(['employee/timesheets']) }}">
                        <a href="{{ route('employee/timesheets') }}">
                            <i class="la la-file-text"></i>
                            <span>My Timesheets</span>
                        </a>
                    </li>
                    <li class="{{ set_active(['employee/overtime']) }}">
                        <a href="{{ route('employee/overtime') }}">
                            <i class="la la-hourglass-half"></i>
                            <span>My Overtime</span>
                        </a>
                    </li>
                    <li class="{{ set_active(['employee/holidays']) }}">
                        <a href="{{ route('employee/holidays') }}">
                            <i class="la la-gift"></i>
                            <span>Holidays</span>
                        </a>
                    </li>
                    <li class="{{ set_active(['my/payslips']) }}">
                        <a href="{{ route('my/payslips') }}">
                            <i class="la la-money"></i>
                            <span>My Payslips</span>
                        </a>
                    </li>
                    <li class="{{ set_active(['learning/catalog','learning/course']) }}">
                        <a href="{{ route('learning/catalog') }}">
                            <i class="la la-book"></i>
                            <span>Learning Catalog</span>
                        </a>
                    </li>
                    <li class="{{ set_active(['performance/tracker','performance/annual/review']) }}">
                        <a href="{{ route('performance/tracker') }}">
                            <i class="la la-line-chart"></i>
                            <span>My Performance</span>
                        </a>
                    </li>
                    <li class="{{ set_active(['performance/annual/review']) }}">
                        <a href="{{ route('performance/annual/review') }}">
                            <i class="la la-check-square-o"></i>
                            <span>Annual Appraisal</span>
                        </a>
                    </li>
                @endif
            </ul>
        </div>
    </div>
</div>
<!-- /Sidebar -->
