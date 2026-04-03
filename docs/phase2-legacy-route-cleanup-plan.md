# Phase 2 Legacy Route Cleanup Plan

Date: 2026-04-03
Scope: Remaining legacy-style web routes after Filament admin cutover.

## Goal
- Remove or migrate legacy `form/*` and similar routes that are no longer needed.
- Keep employee self-service and public recruitment flows intact.
- Reduce route/controller surface area while preserving behavior.

## Preconditions (must be true before deletion)
1. `php artisan route:list --except-vendor` reviewed for affected paths.
2. No current menu/button points to the route being removed.
3. Smoke tests pass before and after each batch.
4. Rollback point exists (tag or commit reference).

## Route Inventory and Action

### A) Safe to remove after replacing callers (legacy admin write endpoints)
These are old admin POST handlers that should be replaced by Filament resource actions.

1. `POST form/holidays/save` -> `HolidayController@saveRecord`
2. `POST form/holidays/update` -> `HolidayController@updateRecord`
3. `POST form/holidays/delete` -> `HolidayController@deleteRecord`
4. `POST form/leaves/save` -> `LeavesController@saveRecord`
5. `POST form/leaves/edit` -> `LeavesController@editRecordLeave`
6. `POST form/leaves/edit/delete` -> `LeavesController@deleteLeave`
7. `POST form/timesheet/save` -> `EmployeeController@saveRecordTimeSheets`
8. `POST form/timesheet/update` -> `EmployeeController@updateRecordTimeSheets`
9. `POST form/timesheet/delete` -> `EmployeeController@deleteRecordTimeSheets`
10. `POST form/overtime/save` -> `EmployeeController@saveRecordOverTime`
11. `POST form/overtime/update` -> `EmployeeController@updateRecordOverTime`
12. `POST form/overtime/delete` -> `EmployeeController@deleteRecordOverTime`

Action:
- Migrate each write flow to Filament action/modal/form submit endpoint.
- Change frontend calls to new endpoints.
- Remove the legacy route only after callsites are gone.

### B) Convert to redirect (if legacy deep-link support still needed)
These currently render legacy pages and should redirect to Filament equivalents.

1. `GET form/holidays/new` -> redirect to `/admin/holidays`
2. `GET form/timesheet/page` -> redirect to `/admin/timesheet-entries`
3. `GET form/overtime/page` -> redirect to `/admin/overtime-entries`

Action:
- Replace current controller GET route with `Route::redirect(...)`.
- Keep for one release cycle, then remove if no usage.

### C) Keep (employee self-service/current flows)
Do not remove in cleanup.

1. `GET attendance/employee/page`
2. `POST attendance/employee/check-in`
3. `POST attendance/employee/check-out`
4. `GET form/leavesemployee/new` (employee leave submit flow; rename later to non-legacy path)
5. `GET my/payslips`
6. `GET my/payslips/{payslip}/download`
7. Performance tracker and annual review routes under `performance/*` (non-`form/*`).
8. Learning catalog routes under `learning/*`.
9. Signature/reference public token routes.

### D) Keep as public recruitment module (not admin migration scope)
These are job/career routes and should be handled in a separate recruitment cleanup.

1. `GET form/job/list`
2. `GET form/job/view/{id}`
3. `POST form/jobs/save`
4. `POST form/apply/job/save`
5. `POST form/apply/job/update`

Optional future step:
- Rename these to `/jobs/*` equivalents, keep redirects for backward compatibility.

### E) Already redirected and can be retired later
All below are already mapped through legacy cutover redirects and can be fully removed in final phase when backward compatibility window closes:

- `all/employee/card`
- `all/employee/list`
- `company/settings/page`
- `roles/permissions/page`
- `localization/page`
- `salary/settings/page`
- `email/settings/page`
- `form/departments/page`
- `form/designations/page`
- `form/leaves/new`
- `form/leavesettings/page`
- `attendance/page`
- `form/shiftscheduling/page`
- `form/shiftlist/page`
- `form/salary/page`
- `form/payroll/items`
- `form/expense/reports/page`
- `form/invoice/reports/page`
- `form/daily/reports/page`
- `form/leave/reports/page`
- `form/payments/reports/page`
- `form/employee/reports/page`
- `form/performance/indicator/page`
- `form/performance/page`
- `form/performance/appraisal/page`
- `form/training/list/page`
- `form/trainers/list/page`
- `form/training/type/list/page`
- `performance/settings/page`

## Recommended Execution Order

1. Batch 1: Convert GET legacy pages (`form/holidays/new`, `form/timesheet/page`, `form/overtime/page`) to redirects.
2. Batch 2: Remove legacy POST write endpoints after verifying no frontend caller remains.
3. Batch 3: Remove cutover compatibility layer (`legacy_admin_cutover` mappings and fallback loop) once old links are retired.
4. Batch 4: Remove migration tracker page/config once governance reporting is no longer needed.

## Validation Checklist per Batch

1. `php -l routes/web.php`
2. `php artisan optimize:clear`
3. `php artisan route:list --except-vendor | rg "form/|all/employee|settings/page|performance/settings/page"`
4. `php artisan test tests/Feature/SmokeTest.php`
5. Manual check:
   - Admin pages load
   - Employee self-service attendance/leave/payslip/performance loads
   - No 404/403 regressions from menu links

