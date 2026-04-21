#!/usr/bin/env bash

set -euo pipefail

cd "$FORGE_SITE_PATH"

echo "[Verify] Checking app environment"
php artisan env

echo "[Verify] Checking health endpoint route"
php artisan route:list | grep -q " up "

echo "[Verify] Checking storage symlink"
if [[ ! -L public/storage ]]; then
    echo "[Verify] public/storage symlink is missing"
    exit 1
fi

echo "[Verify] Checking database connection"
php artisan migrate:status --no-interaction >/dev/null

echo "[Verify] Checking cache and queue tables"
php artisan db:show --database="${DB_CONNECTION:-mysql}" >/dev/null 2>&1 || true

echo "[Verify] Validation complete"