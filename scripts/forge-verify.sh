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

echo "[Verify] Checking built frontend assets"
if [[ ! -f public/build/manifest.json ]]; then
    echo "[Verify] public/build/manifest.json is missing"
    exit 1
fi

if ! grep -q 'resources/css/app.css' public/build/manifest.json; then
    echo "[Verify] app.css entry is missing in Vite manifest"
    exit 1
fi

if ! grep -q 'resources/js/app.js' public/build/manifest.json; then
    echo "[Verify] app.js entry is missing in Vite manifest"
    exit 1
fi

echo "[Verify] Validation complete"