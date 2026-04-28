# Simplicitea Deployment Guide (Laravel Forge)

This document prepares the Simplicitea POS system for production deployment via Laravel Forge.

## 1. Prerequisites

- Domain/subdomain already pointed to the Forge server IP
- A Forge server with:
  - PHP 8.2+
  - MySQL
  - Node.js 20+
- Git repository connected in Forge

## 2. Create The Forge Site

1. In Forge, create a new site.
2. Set the web directory to:

   /home/forge/<your-site>/public

3. Connect your repository and branch (for example: main).

## 3. Production Environment Variables

Use Forge Environment and set values like these:

Tip: you can copy from .env.forge.example in this repository.

APP_NAME="Icy's Simplicitea POS"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

APP_MAINTENANCE_DRIVER=file

LOG_CHANNEL=stack
LOG_STACK=daily
LOG_LEVEL=warning

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=simplicitea
DB_USERNAME=forge
DB_PASSWORD=your_strong_password

SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=your-domain.com
SESSION_SECURE_COOKIE=true

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=public
QUEUE_CONNECTION=database

CACHE_STORE=database

MAIL_MAILER=smtp
MAIL_SCHEME=tls
MAIL_HOST=smtp.mailprovider.com
MAIL_PORT=587
MAIL_USERNAME=your_mail_username
MAIL_PASSWORD=your_mail_password
MAIL_FROM_ADDRESS=alerts@your-domain.com
MAIL_FROM_NAME="Icy's Simplicitea POS"

PAYMONGO_SECRET_KEY=your_paymongo_secret
PAYMONGO_BASE_URL=https://api.paymongo.com/v1

VITE_APP_NAME="Icy's Simplicitea POS"

Important:
- Run Generate App Key in Forge if APP_KEY is empty.
- Keep APP_DEBUG=false in production.

## 4. Forge Deployment Script

In Forge site deployment script, use:

cd $FORGE_SITE_PATH
bash scripts/forge-deploy.sh

Reference script location in this repository:

scripts/forge-deploy.sh

This script now includes:
- Automatic recovery from maintenance mode on failures
- Dependency and build steps
- Migration + cache optimization
- Queue restart
- Post-deploy verification via scripts/forge-verify.sh

## 5. Scheduler Setup

This app runs scheduled stock alerts from bootstrap/app.php. In Forge, enable Scheduler for the site.

Forge scheduler command:

php /home/forge/<your-site>/artisan schedule:run

Frequency:

* * * * *

## 6. Queue Worker Setup

This app uses database queue. In Forge, create a daemon:

php /home/forge/<your-site>/artisan queue:work database --sleep=3 --tries=3 --max-time=3600

Recommended daemon settings:
- Processes: 1 to 2
- Auto-restart: enabled
- User: forge

## 7. Nginx Site Configuration

Use Forge default Laravel Nginx config. Ensure there is no extra basic auth or IP restriction in production.

Confirm these headers are present (Forge usually includes them):
- X-Forwarded-For
- X-Forwarded-Proto

## 8. First Deployment Checklist

1. Deploy from Forge.
2. Verify migrations succeeded.
3. Verify storage symlink exists:

   ls -la public/storage

4. Verify health endpoint:

   https://your-domain.com/up

5. Verify login and POS dashboard load.
6. Verify queue daemon is running.
7. Verify scheduler is enabled.

## 9. Post-Deployment Hardening

- Enable automatic security updates on the server.
- Restrict SSH access to known IPs.
- Configure daily database backups in Forge.
- Add uptime monitoring for /up endpoint.

## 10. DigitalOcean Production Baseline

Use this baseline for stable production hosting on DigitalOcean:

1. Droplet size
- Minimum 2 vCPU / 4 GB RAM for POS workloads with queue + scheduler.

2. Storage
- Use Premium SSD.
- Keep at least 30% free disk space for logs, cache, and deployment artifacts.

3. Firewall
- Allow inbound only: 22 (restricted to your IP), 80, 443.
- Deny all other inbound ports.

4. Database
- Prefer managed database for easier backups and failover.
- If local MySQL is used, enable automated backups and strong passwords.

5. Backups
- Enable Droplet backups in DigitalOcean.
- Enable database backups (daily).

6. Monitoring
- Enable DigitalOcean monitoring/alerts for CPU, RAM, disk, and uptime.
- Add a Forge monitor for https://your-domain.com/up

## 11. First Production Deploy (Exact Order)

1. Create server and site in Forge.
2. Configure SSL and force HTTPS in Forge.
3. Set Forge environment using .env.forge.example.
4. Ensure APP_KEY is generated in Forge.
5. Configure scheduler in Forge (* * * * *).
6. Configure queue daemon in Forge.
7. Set deployment script to run scripts/forge-deploy.sh.
8. Run first deploy.
9. Verify login, POS flow, reports, and queue processing.

## 12. Create Admin And Employee Accounts On Forge

Use this after first deploy (or any time you need to reset default credentials).

1. Add these environment variables in Forge (Site > Environment):

FORGE_ADMIN_EMAIL=admin@simplicitea.com
FORGE_ADMIN_PASSWORD=your_strong_admin_password
FORGE_EMPLOYEE_EMAIL=employee@simplicitea.com
FORGE_EMPLOYEE_PASSWORD=your_strong_employee_password

2. Run the seeder from Forge server (recommended):

php artisan db:seed --class=Database\\Seeders\\ForgeUserSeeder --force

Notes:
- Employee account is created with role cashier (the app's employee role).
- Seeder is idempotent and uses updateOrCreate by email.

3. Tinker fallback (single command) if you do not want to run db:seed:

php artisan tinker --execute="(new \\Database\\Seeders\\ForgeUserSeeder())->run();"

## 13. Fix Missing Branches In Production

If branch dropdowns/selectors are empty, the `branches` table is likely empty or all rows are inactive.

1. Create/update the default branches from Forge command line:

php artisan tinker --execute='\App\Models\Branch::updateOrCreate(["name"=>"Oslob Main"],["address"=>"Main Street, Oslob, Cebu","phone"=>"+63 912 345 6789","manager_name"=>"Maria Santos","is_active"=>true]); \App\Models\Branch::updateOrCreate(["name"=>"Santander Poblacion"],["address"=>"Poblacion, Santander, Cebu","phone"=>"+63 912 345 6788","manager_name"=>"Juan dela Cruz","is_active"=>true]); \App\Models\Branch::updateOrCreate(["name"=>"Looc Branch"],["address"=>"Looc, Oslob, Cebu","phone"=>"+63 912 345 6787","manager_name"=>"Ana Garcia","is_active"=>true]); echo "BRANCH_SEEDED".PHP_EOL;'

2. Verify at least one active branch exists:

php artisan tinker --execute='echo "ACTIVE_BRANCHES=".\App\Models\Branch::where("is_active", true)->count().PHP_EOL;'

3. Clear caches if UI still does not show branches:

php artisan optimize:clear
