# Purple HR SMB v1

## Positioning
Purple HR SMB v1 is the mid-tier Laravel product line for small and medium-sized organizations that need a practical HR operating system without enterprise workflow complexity.

This product is intended to be:
- operationally reliable
- simple to onboard
- credible in demos and customer trials
- focused on day-to-day HR administration

This product is not the enterprise platform. The separate scalable rebuild remains the long-term enterprise track.

## Included Modules
The v1 commercial surface includes:
- Authentication
- User Management
- Company Settings
- Roles
- Employees
- Departments
- Designations
- Holidays
- Leave Management
- Attendance
- Timesheets
- Overtime
- Payroll
- Payslip View and Export
- Basic Reports

## Product Goals
The v1 release should enable an SMB customer to:
- provision internal users safely
- maintain an employee directory
- organize teams through departments and designations
- record leave requests and leave history
- monitor attendance records
- track timesheets and overtime submissions
- manage payroll records and payslips
- update company profile settings
- operate with basic role separation for administrators and internal staff

## Explicitly Deferred
The following areas are out of scope for SMB v1 and should stay hidden or de-emphasized until they are productized properly:
- Performance management and goals/objectives
- Recruitment and job-board workflows
- Training administration
- Sales, invoices, and expense-heavy finance workflows outside core payroll
- Shift scheduling and shift list workflows
- Asset management
- Advanced workflow builders
- Fine-grained policy-based RBAC
- Enterprise analytics and AI features

## Stability Bar
A module is considered in-scope for v1 only if it satisfies all of the following:
- main list and detail flows render with real data
- create, update, and delete paths are safe enough for internal admin use
- destructive actions do not rely on `GET`
- navigation and page copy reflect Purple HR rather than the original template
- the module is covered by at least smoke-level regression checks where practical

## Near-Term Engineering Priorities
1. Keep polishing only the in-scope modules.
2. Hide or defer modules outside the v1 boundary.
3. Improve role enforcement across shipped modules.
4. Strengthen smoke coverage for the full commercial path.
5. Prepare setup, demo, and deployment documentation for customer-facing use.
