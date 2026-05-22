#!/usr/bin/env bash
# Idempotent setup runs on container start.
# Installs PHP deps, runs migrations, seeds admin, builds front assets.
set -e

cd /var/www/html

echo "[init] composer install"
if [ ! -d vendor ] || [ ! -f vendor/autoload.php ] || [ ! -d vendor/twilio ]; then
    composer install --no-interaction --prefer-dist --optimize-autoloader
fi

echo "[init] .env / app key"
if [ ! -f .env ] && [ -f .env.example ]; then
    cp .env.example .env
fi
if [ -f .env ] && ! grep -q "^APP_KEY=base64:" .env; then
    php artisan key:generate --force
fi

echo "[init] sqlite db file"
if grep -q "^DB_CONNECTION=sqlite" .env 2>/dev/null; then
    mkdir -p database
    [ -f database/database.sqlite ] || touch database/database.sqlite
fi

echo "[init] migrate"
php artisan migrate --force --no-interaction || true

echo "[init] seed admin + contest content"
php artisan db:seed --class=AdminSeeder --force --no-interaction || true
php artisan db:seed --class=ContestContentSeeder --force --no-interaction || true

echo "[init] npm install + build"
if [ ! -f public/build/manifest.json ]; then
    [ -d node_modules ] || npm ci --no-audit --no-fund || npm install --no-audit --no-fund
    npm run build
fi

echo "[init] cache clear"
php artisan optimize:clear || true

echo "[init] done"
exit 0
