# Filament Rebuild Sprint 1 Checklist

## Goal
Establish Filament foundation and deliver first two production resources:
- Departments
- Designations

## Tasks
1. Install Filament and dependencies.
2. Create admin panel provider and configure routing.
3. Configure auth access to panel.
4. Define navigation groups:
   - Organization
   - People
   - Time and Attendance
   - Payroll
   - Reports
   - Settings
5. Scaffold and wire policies for:
   - User
   - Department
   - PositionType (Designations)
6. Build `DepartmentResource`:
   - list/search/sort
   - create/edit/delete actions
   - policy checks
7. Build `DesignationResource` (`PositionType`):
   - list/search/sort
   - create/edit/delete actions
   - policy checks
8. Add smoke tests for both resources.
9. Add seed data for quick QA where needed.
10. Document local run and QA steps.

## Definition of Done
- `/admin` panel loads and requires authentication.
- Department and designation CRUD works from panel.
- Unauthorized users cannot access restricted actions.
- Smoke tests pass locally.
