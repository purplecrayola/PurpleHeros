# Purple Crayola v3 UX Sweep

## Scope
- Employee module (Blade shell routes)
- Admin module (Filament + legacy Blade admin routes)

## Phase 1 (Completed)
- Global design-system foundations applied to both modules.
- Unified heading hierarchy (Playfair display headings + Inter UI text).
- Unified controls (button radius/height, form borders, table shell consistency).
- Accessible interaction states (`:focus-visible`) across links/buttons/inputs.
- Sidebar active state consistency and signal-color usage reduced to accents.

## Phase 2 (In Progress)
- Page-level consistency checks for top spacing, filter rows, and action bars.
- Performance module employee pages normalized.
- Values Catalog upgraded from free-text to structured catalog editor.

## Remaining sweep checklist
- Standardize empty-state cards and no-data messaging across all modules.
- Standardize filter/action bars for Reports, Payroll, Attendance admin pages.
- Standardize table action column patterns (button weight, spacing, order).
- Verify mobile nav behavior across Filament resource pages.
- Add contextual help copy to high-complexity settings pages.

## Acceptance criteria
- Typography hierarchy consistent on all key employee/admin pages.
- Buttons/inputs/tables have shared dimensions and borders.
- Focus-visible present on all interactive controls.
- Sidebar selection and hover states coherent and accessible.
- No module uses heavy gradients as default page background.
