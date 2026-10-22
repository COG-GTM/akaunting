#!/bin/sh
set -e

cd /var/www/html

# The generated .env (app key, DB prefix, install marker) lives on a volume so a
# recreated container reuses the existing installation instead of reinstalling.
STATE_DIR="${AKAUNTING_STATE_DIR:-/var/lib/akaunting}"
mkdir -p "$STATE_DIR"

if [ -f "$STATE_DIR/.env" ]; then
    ln -sfn "$STATE_DIR/.env" .env
fi

if ! grep -q '^APP_INSTALLED=true' .env 2>/dev/null; then
    rm -f .env

    echo "Waiting for database ${DB_HOST}:${DB_PORT:-3306}..."
    until php -r 'try { new PDO("mysql:host=" . getenv("DB_HOST") . ";port=" . (getenv("DB_PORT") ?: 3306), getenv("DB_USERNAME"), getenv("DB_PASSWORD")); } catch (Exception $e) { exit(1); }'; do
        sleep 2
    done

    if ! php artisan install --no-interaction \
        --db-host="${DB_HOST}" \
        --db-port="${DB_PORT:-3306}" \
        --db-name="${DB_DATABASE}" \
        --db-username="${DB_USERNAME}" \
        --db-password="${DB_PASSWORD}" \
        --db-prefix="${DB_PREFIX:-}" \
        --company-name="${AKAUNTING_COMPANY_NAME:-My Company}" \
        --company-email="${AKAUNTING_COMPANY_EMAIL:-my@company.com}" \
        --admin-email="${AKAUNTING_ADMIN_EMAIL}" \
        --admin-password="${AKAUNTING_ADMIN_PASSWORD}" \
        --locale="${APP_LOCALE:-en-GB}"; then
        # Don't leave a half-written .env (APP_DEBUG=true) behind for the web server.
        rm -f .env
        exit 1
    fi

    if [ -n "${APP_URL}" ]; then
        sed -i "s|^APP_URL=.*|APP_URL=${APP_URL}|" .env
    fi

    mv .env "$STATE_DIR/.env"
    ln -sfn "$STATE_DIR/.env" .env
fi

chown -R www-data:www-data storage bootstrap/cache "$STATE_DIR"

exec docker-php-entrypoint "$@"
