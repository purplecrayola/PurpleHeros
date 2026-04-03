# Classic vs Rebuild Parity Checklist

## Scope
This checklist compares legacy Laravel controller behavior against the Filament rebuild for core HR operations.

## Current Status Summary
- Completed parity items: `29`
- In-progress parity items: `0`
- Deferred parity items: `2`

## Authentication and Access
- [x] Admin panel access is role-gated in rebuild.
- [x] Role-specific navigation visibility is enforced.
- [x] Non-admin employee is blocked from `/admin`.
- [x] UAT role matrix is covered by automated test.

## Employee Management
- [x] Employee create/edit requires `name`.
- [x] Employee create/edit requires `email` and validates email format.
- [x] Employee create/edit requires `employee_id`.
- [x] Employee `employee_id` must map to a real user (`users.user_id`).
- [x] Employee `employee_id` uniqueness enforced.
- [x] Employee create/edit requires `gender`.
- [x] Employee create/edit requires `birth_date`.
- [x] Enforce exact legacy create pairing (`employee_id` + matching user email) in Filament form logic.

## Leave Management
- [x] Leave request requires `user_id`, `leave_type`, `from_date`, `to_date`, `leave_reason`.
- [x] Leave `to_date >= from_date` enforced.
- [x] Leave total days is auto-calculated in rebuild form (legacy behavior parity).
- [x] Leave status defaults to `Pending`.
- [x] Approve/Reject workflow with approver metadata exists.
- [x] Duplicate + overlap leave collision policy enforced (app-level, with signature uniqueness for exact duplicates).

## Attendance
- [x] Attendance status taxonomy parity (`Present`, `Absent`, `Late`, `Remote`).
- [x] Attendance filter parity (month/year + employee equivalents now present in rebuild filters).
- [x] Attendance analytics widgets and summaries available.
- [x] Add monthly attendance summary export action in Filament with classic-style totals.

## Timesheets
- [x] Timesheet requires `user_id`, `work_date`, `project_name`.
- [x] Timesheet hours numeric bounds aligned to legacy (`0..24`).
- [x] Cross-field guard: `worked_hours` cannot exceed `assigned_hours + 4`.
- [x] Timesheet description max length parity (`<=1000`).
- [x] Timesheet starter UAT scenario data available.

## Overtime
- [x] Overtime requires `user_id`, `ot_date`, `hours`, `ot_type`, `status`.
- [x] Overtime hours bounds aligned to legacy (`0.5..24`).
- [x] Overtime status defaults to `Pending`.
- [x] Overtime approval/rejection metadata and actions available.
- [x] Overtime reason/description max lengths aligned to legacy.

## Payroll
- [x] Payroll requires employee linkage (`user_id`) and per-user uniqueness.
- [x] Payroll requires all component fields (legacy parity).
- [x] Numeric validation applied across salary components.
- [x] Payroll table filter parity (employee + salary band).
- [x] Recreate classic salary PDF/Excel detail flow inside Filament row actions.

## Data Parity and UAT
- [x] `rebuild:core-parity` command available for parity metrics.
- [x] `rebuild:uat-users` command available for role-specific demo users.
- [x] `rebuild:uat-bootstrap` one-shot command available.
- [x] UAT playbook documented with credentials and scenarios.

## Recommended Next Fixes (Priority Order)
1. Optional: modernize salary PDF/XLSX templates for branded output while keeping column parity.
2. Optional: formalize leave overlap policy UI messaging (inline hints + policy banner on leave form).
