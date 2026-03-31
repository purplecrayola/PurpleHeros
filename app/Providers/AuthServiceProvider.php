<?php

namespace App\Providers;

use App\Models\department;
use App\Models\Employee;
use App\Models\positionType;
use App\Models\CompanySettings;
use App\Models\User;
use App\Models\Holiday;
use App\Models\LeavesAdmin;
use App\Models\AttendanceRecord;
use App\Models\TimesheetEntry;
use App\Models\OvertimeEntry;
use App\Models\StaffSalary;
use App\Models\ExportAudit;
use App\Models\LeavePolicyBand;
use App\Policies\DepartmentPolicy;
use App\Policies\EmployeePolicy;
use App\Policies\PositionTypePolicy;
use App\Policies\CompanySettingsPolicy;
use App\Policies\UserPolicy;
use App\Policies\HolidayPolicy;
use App\Policies\LeavesAdminPolicy;
use App\Policies\AttendanceRecordPolicy;
use App\Policies\TimesheetEntryPolicy;
use App\Policies\OvertimeEntryPolicy;
use App\Policies\StaffSalaryPolicy;
use App\Policies\ExportAuditPolicy;
use App\Policies\LeavePolicyBandPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        User::class => UserPolicy::class,
        Employee::class => EmployeePolicy::class,
        department::class => DepartmentPolicy::class,
        positionType::class => PositionTypePolicy::class,
        CompanySettings::class => CompanySettingsPolicy::class,
        Holiday::class => HolidayPolicy::class,
        LeavesAdmin::class => LeavesAdminPolicy::class,
        AttendanceRecord::class => AttendanceRecordPolicy::class,
        TimesheetEntry::class => TimesheetEntryPolicy::class,
        OvertimeEntry::class => OvertimeEntryPolicy::class,
        StaffSalary::class => StaffSalaryPolicy::class,
        ExportAudit::class => ExportAuditPolicy::class,
        LeavePolicyBand::class => LeavePolicyBandPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        //
    }
}
