# Purple HR SMB v1

Purple HR SMB v1 is the mid-tier Laravel product line for small and medium-sized organizations that need a practical HR operating system without enterprise workflow complexity.

This repository is the stabilized Laravel codebase for the SMB product track. The separate greenfield rebuild remains the enterprise track.

## Current Product Surface
In-scope modules for this repo are defined in [docs/purple-hr-smb-v1.md](docs/purple-hr-smb-v1.md).

Core modules currently exposed in the app:
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

## Runtime Requirements
- PHP `8.2+`
- MySQL `8+` or compatible MariaDB
- Composer
- Node.js and npm

Laravel packages in use include:
- `barryvdh/laravel-dompdf`
- `brian2694/laravel-toastr`
- `maatwebsite/excel`
- `laravel/ui`

## Local Setup
1. Clone the repository.
2. Copy the environment file:
   ```bash
   cp .env.example .env
   ```
3. Update database settings in `.env`.
4. Install PHP dependencies:
   ```bash
   composer install
   ```
5. Install frontend dependencies:
   ```bash
   npm install
   ```
6. Generate the application key:
   ```bash
   php artisan key:generate
   ```
7. Create the database:
   ```sql
   CREATE DATABASE hr_database CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
8. Run migrations and seeders:
   ```bash
   php artisan migrate:fresh --seed
   ```
9. Build frontend assets:
   ```bash
   npm run build
   ```
10. Start the local server:
    ```bash
    php artisan serve --host=127.0.0.1 --port=8003
    ```

App URL:
- `http://127.0.0.1:8003`

Seeded admin for local use:
- Email: `admin@purplecrayola.com`
- Password: `Password123!`

## Test Commands
Run the smoke suite:
```bash
php artisan test --filter=SmokeTest
```

Run the full suite:
```bash
php artisan test
```

## Deployment Notes
This repo is intended for a conventional Laravel deployment target such as:
- VPS
- managed Laravel hosting
- container-based hosting

It is not designed around shared hosting constraints.

See [docs/setup-and-deployment.md](docs/setup-and-deployment.md) for the deployment checklist.

## Important Notes
- This repo is being productized as the SMB track, not the enterprise platform.
- Deferred modules should remain hidden or de-emphasized until they are productized.
- Role enforcement is still being tightened across the shipped modules.
