#!/usr/bin/env bash

set -euo pipefail

cd "$FORGE_SITE_PATH"

echo "[Deploy] Putting application in maintenance mode"
php artisan down || true

echo "[Deploy] Pulling latest code"
git pull origin "$FORGE_SITE_BRANCH"

echo "[Deploy] Installing PHP dependencies"
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

echo "[Deploy] Installing Node dependencies and building assets"
npm ci
npm run build

echo "[Deploy] Running database migrations"
php artisan migrate --force

echo "[Deploy] Refreshing generated artifacts"
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "[Deploy] Ensuring storage symlink exists"
php artisan storage:link || true

echo "[Deploy] Restarting queue workers"
php artisan queue:restart || true

echo "[Deploy] Bringing application back online"
php artisan up

echo "[Deploy] Completed successfully"