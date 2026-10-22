#!/bin/sh
set -e

cd /var/www/html

if ! grep -q '^APP_INSTALLED=true' .env 2>/dev/null; then
    echo "Waiting for database ${DB_HOST}:${DB_PORT:-3306}..."
    until php -r 'try { new PDO("mysql:host=" . getenv("DB_HOST") . ";port=" . (getenv("DB_PORT") ?: 3306), getenv("DB_USERNAME"), getenv("DB_PASSWORD")); } catch (Exception $e) { exit(1); }'; do
        sleep 2
    done

    php artisan install --no-interaction \
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
        --locale="${APP_LOCALE:-en-GB}"

    if [ -n "${APP_URL}" ]; then
        sed -i "s|^APP_URL=.*|APP_URL=${APP_URL}|" .env
    fi

    chown -R www-data:www-data storage bootstrap/cache .env
fi

exec docker-php-entrypoint "$@"
