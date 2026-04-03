<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Legacy Admin Cutover
    |--------------------------------------------------------------------------
    |
    | Central map of legacy Blade admin paths that should redirect to the
    | Filament admin panel equivalents. Keep this list small and intentional.
    |
    */
    'enabled' => env('LEGACY_ADMIN_CUTOVER_ENABLED', true),

    'legacy_to_filament' => [
        'form/departments/page' => '/admin/departments',
        'form/designations/page' => '/admin/position-types',
        'all/employee/card' => '/admin/employees',
        'all/employee/list' => '/admin/employees',
        'form/leaves/new' => '/admin/leaves-admins',
        'attendance/page' => '/admin/attendance-records',
        'form/leavesettings/page' => '/admin/leave-settings',
        'form/shiftscheduling/page' => '/admin/shift-scheduling',
        'form/shiftlist/page' => '/admin/shift-scheduling',
        'form/salary/page' => '/admin/staff-salaries',
        'form/payroll/items' => '/admin/payroll-policy-sets',
        'form/expense/reports/page' => '/admin/reports-hub',
        'form/invoice/reports/page' => '/admin/reports-hub',
        'form/employee/reports/page' => '/admin/reports-hub',
        'form/leave/reports/page' => '/admin/reports-hub',
        'form/daily/reports/page' => '/admin/reports-hub',
        'form/payments/reports/page' => '/admin/reports-hub',
        'form/performance/indicator/page' => '/admin/performance-hub',
        'form/performance/page' => '/admin/performance-hub',
        'form/performance/appraisal/page' => '/admin/performance-hub',
        'company/settings/page' => '/admin/company-settings',
        'localization/page' => '/admin/company-settings',
        'roles/permissions/page' => '/admin/roles-permissions',
        'email/settings/page' => '/admin/email-settings',
        'salary/settings/page' => '/admin/payroll-defaults',
        'performance/settings/page' => '/admin/performance-settings',
        'performance/team/reviews' => '/admin/performance-hub',
        'performance/team/annual-reviews' => '/admin/performance-hub',
        'form/training/list/page' => '/admin/trainings',
        'form/trainers/list/page' => '/admin/trainers',
        'form/training/type/list/page' => '/admin/training-types',
    ],
];
