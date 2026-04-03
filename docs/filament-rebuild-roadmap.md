# Purple HR Filament Rebuild Roadmap

## Objective
Rebuild Purple HR SMB on a modern Filament + Livewire architecture while reusing stable domain/data logic from the current Laravel app.

This is a rebuild, not a 1:1 lift-and-shift migration of Blade routes/views.

## Product Boundary (SMB Core Only)
In-scope modules for initial rebuild:
- Authentication and access control
- User management
- Company settings
- Roles and permissions
- Employees
- Departments
- Designations
- Holidays
- Leave management
- Attendance
- Timesheets
- Overtime
- Payroll
- Payslip export
- Basic reports

Deferred modules:
- Jobs/recruitment
- Training
- Performance
- Sales/estimates/expenses (outside payroll core)

## Target Architecture
- Laravel 11 + Filament 3 panel(s)
- Livewire-driven admin UI with server-side forms/tables/actions
- Policy-based authorization across all resources/actions
- Service layer for domain rules (leave balances, overtime, payroll calculations)
- Existing DB schema reused initially to reduce cutover risk
- Feature flags to run old/new UI in parallel during transition

## UX Direction (AlignmentX-like)
- Minimal, modern information density
- Consistent table and form patterns across modules
- Tokenized branding from company settings (colors/logo)
- Clear status badges, quick filters, bulk actions
- Zero template-era clutter in navigation and page copy
- Figma-first flow for key screens before implementation

## Delivery Phases

### Phase 0: Foundation
- Install and configure Filament panel
- Configure authentication flow and base layout
- Wire roles/permissions + policies baseline
- Create navigation groups aligned to SMB scope
- Add shared table/form conventions

### Phase 1: Org and Access
- Company settings resource/page
- User management resource
- Roles/permissions management
- Departments resource
- Designations resource

### Phase 2: Workforce Core
- Employees resource (list/detail/edit)
- Holidays resource
- Leave management resource
- Attendance resource

### Phase 3: Time and Pay
- Timesheets resource
- Overtime resource
- Payroll resource
- Payslip view/export flow

### Phase 4: Reports and Hardening
- Basic reports pages
- Role matrix hardening
- Regression and smoke tests for core flows
- Performance pass on heavy tables/filters

### Phase 5: Cutover
- Enable new panel by default
- Remove/retire old Blade modules in-scope for SMB
- Keep deferred modules hidden until productized

## Data and Logic Reuse Strategy
Reuse:
- Existing Eloquent models and relationships (where sane)
- Calculation logic from payroll/leave/timesheet controllers
- Existing export pipelines (PDF/Excel), wrapped behind services

Refactor during rebuild:
- Move controller-heavy business logic into dedicated service classes
- Normalize inconsistent model naming where low-risk
- Replace GET destructive flows with explicit action methods

## Quality Gates
Each rebuilt module must pass:
- CRUD path works with real data
- No destructive action via GET
- Authorization policies enforced for view/create/update/delete
- Smoke tests for critical happy paths
- Navigation/page language aligned to Purple HR SMB

## Sprint 1 (Immediate Build Slice)
- Filament installed and bootstrapped
- Admin panel auth and sidebar groups configured
- Policies scaffolded for core models
- Departments and Designations resources live
- Basic smoke tests for both resources

## Risks and Mitigation
- Risk: Hidden logic in legacy controllers.
  - Mitigation: Extract service classes before/while implementing corresponding resource.
- Risk: Permission leaks during parallel run.
  - Mitigation: Policy-first build, role matrix tests, restricted default access.
- Risk: UI inconsistency as modules scale.
  - Mitigation: Shared Filament form/table conventions and component patterns.

## Working Model with Figma
- Design high-impact pages in Figma first (employee detail, payroll run, leave approval queue).
- Implement using shared UI tokens/components from the panel theme.
- Run quick visual QA against Figma before marking modules complete.
