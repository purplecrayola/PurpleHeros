# Purple HR Rebuild UAT Playbook

## Bootstrap
Run this from the project root:

```bash
php artisan rebuild:uat-bootstrap
```

This command creates/updates role-based UAT users, seeds scenario records, and runs core parity sync data.

## Default Password
All UAT users use:

`Password123!`

## UAT Accounts
- `admin@purplecrayola.com` (`Super Admin`)
- `hr.manager@purplecrayola.com` (`HR Manager`)
- `payroll.admin@purplecrayola.com` (`Payroll Admin`)
- `ops.manager@purplecrayola.com` (`Operations Manager`)
- `reports.analyst@purplecrayola.com` (`Reports Analyst`)
- `employee@purplecrayola.com` (`Employee`)

## Login URL
- `http://127.0.0.1:8007/admin/login`

## Role Scenarios
1. Super Admin
- Validate access to all navigation groups.
- Open Users, Company Settings, Reports Hub, Payroll.

2. HR Manager
- Validate access to Departments, Employees, Attendance/Leaves/Overtime, Reports Hub.
- Validate Payroll and Users pages are blocked.
- Confirm approved leave scenario exists for this user.

3. Payroll Admin
- Validate access to Payroll and Reports Hub.
- Validate Departments and Users are blocked.
- Confirm salary record exists.

4. Operations Manager
- Validate access to Attendance, Leave Requests, Overtime.
- Validate Reports Hub and Users are blocked.
- Confirm approved overtime scenario exists for this user.

5. Reports Analyst
- Validate access to Reports Hub and Export Audits.
- Validate Attendance, Payroll, and Users are blocked.

6. Employee
- Validate `/admin` is blocked.
- Confirm pending leave/overtime scenarios exist in data (for manager-side review flows).

## Quick Reset
To safely refresh UAT data without duplicates:

```bash
php artisan rebuild:uat-bootstrap
```

The commands are idempotent; reruns update and top-up data rather than duplicating core seeded scenarios.
