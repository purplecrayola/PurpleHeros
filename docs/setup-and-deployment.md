# Setup And Deployment

## Local Development Setup
Recommended local stack:
- PHP 8.2+
- MySQL 8+
- Composer
- Node.js and npm

### Environment
Start from `.env.example` and set at minimum:
- `APP_NAME="Purple HR"`
- `APP_ENV=local`
- `APP_DEBUG=true`
- `APP_URL=http://127.0.0.1:8003`
- `DB_CONNECTION=mysql`
- `DB_HOST=127.0.0.1`
- `DB_PORT=3306`
- `DB_DATABASE=hr_database`
- `DB_USERNAME=your_db_user`
- `DB_PASSWORD=your_db_password`

### Install
```bash
composer install
npm install
php artisan key:generate
php artisan migrate:fresh --seed
npm run build
php artisan serve --host=127.0.0.1 --port=8003
```

### Local Validation
Minimum validation before active use:
```bash
php artisan test --filter=SmokeTest
php artisan test
```

## Deployment Target
Use a standard Laravel-capable environment with:
- PHP-FPM or equivalent
- Nginx or Apache
- MySQL
- writable `storage/` and `bootstrap/cache/`
- scheduled backups for the application database

Recommended deployment target:
- small VPS or managed Laravel host for SMB customers

## Production Environment Baseline
Minimum production settings:
- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://your-domain`
- production database credentials
- real SMTP credentials instead of local Mailpit settings
- appropriate `MAIL_FROM_ADDRESS`
- appropriate `MAIL_FROM_NAME`

## Production Deploy Checklist
1. Provision server and database.
2. Clone the repository onto the target host.
3. Create `.env` with production credentials.
4. Run:
   ```bash
   composer install --no-dev --optimize-autoloader
   npm install
   npm run build
   php artisan key:generate
   php artisan migrate --force
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```
5. Ensure write permissions for:
   - `storage/`
   - `bootstrap/cache/`
6. Point the web root to `public/`.
7. Configure HTTPS.
8. Validate login, employees, leave, attendance, timesheet, overtime, payroll, settings, and reports.

## Operational Notes
- This product line is the SMB track, not the enterprise platform.
- Keep navigation aligned to the scope in `docs/purple-hr-smb-v1.md`.
- Do not expose deferred modules until they are productized and tested.
- Prefer running the smoke suite after every stabilization pass.

## Immediate Hardening Priorities
These are still active engineering priorities:
1. tighten RBAC across shipped modules
2. improve setup/deployment automation
3. review reports and holidays for release readiness
4. continue removing remaining template residue from in-scope pages
