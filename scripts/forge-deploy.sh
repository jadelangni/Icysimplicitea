#!/usr/bin/env bash

set -euo pipefail

cd "$FORGE_SITE_PATH"

APP_WAS_PUT_DOWN=0

cleanup() {
	exit_code=$?

	# Prevent prolonged downtime if deployment fails after maintenance mode.
	if [[ $APP_WAS_PUT_DOWN -eq 1 ]]; then
		php artisan up >/dev/null 2>&1 || true
	fi

	if [[ $exit_code -ne 0 ]]; then
		echo "[Deploy] Failed with exit code $exit_code"
	fi

	exit $exit_code
}

trap cleanup EXIT

echo "[Deploy] Checking required tooling"
command -v php >/dev/null 2>&1 || { echo "[Deploy] php is required"; exit 1; }
command -v composer >/dev/null 2>&1 || { echo "[Deploy] composer is required"; exit 1; }
command -v npm >/dev/null 2>&1 || { echo "[Deploy] npm is required"; exit 1; }

echo "[Deploy] Putting application in maintenance mode"
php artisan down || true
APP_WAS_PUT_DOWN=1

echo "[Deploy] Pulling latest code"
git pull origin "$FORGE_SITE_BRANCH"

echo "[Deploy] Installing PHP dependencies"
composer install --no-interaction --prefer-dist --optimize-autoloader --no-dev

echo "[Deploy] Installing Node dependencies and building assets"
if [[ -f package-lock.json ]]; then
	npm ci
else
	npm install
fi
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
APP_WAS_PUT_DOWN=0

echo "[Deploy] Running post-deploy verification"
bash scripts/forge-verify.sh

echo "[Deploy] Completed successfully"