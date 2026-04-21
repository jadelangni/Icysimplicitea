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
