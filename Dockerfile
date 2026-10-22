# syntax=docker/dockerfile:1
ARG PHP_VERSION=8.4.27

FROM node:20-bookworm-slim AS assets
WORKDIR /app
COPY package.json package-lock.json .npmrc webpack.mix.js tailwind.config.js presets.js safelist.txt ./
RUN npm ci --no-audit --no-fund
COPY resources ./resources
COPY public ./public
COPY app ./app
COPY modules ./modules
RUN npm run production

FROM php:${PHP_VERSION}-apache AS base

COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

RUN apt-get update \
    && apt-get install -y --no-install-recommends git unzip \
    && rm -rf /var/lib/apt/lists/* \
    && install-php-extensions bcmath gd intl zip pdo_mysql exif opcache \
    && a2enmod rewrite \
    && sed -i 's/<VirtualHost \*:80>/<VirtualHost *:*>/' /etc/apache2/sites-available/000-default.conf \
    && printf '<Directory /var/www/html>\n    AllowOverride All\n</Directory>\n' > /etc/apache2/conf-available/akaunting.conf \
    && a2enconf akaunting \
    && printf 'memory_limit=512M\nupload_max_filesize=32M\npost_max_size=32M\n' > /usr/local/etc/php/conf.d/akaunting.ini

WORKDIR /var/www/html
ENV COMPOSER_ALLOW_SUPERUSER=1

FROM base AS app

COPY composer.json composer.lock ./
RUN composer install --prefer-dist --no-interaction --no-scripts --no-autoloader --no-progress

COPY . .
COPY --from=assets /app/public/js ./public/js
COPY --from=assets /app/public/css ./public/css
COPY --from=assets /app/public/mix-manifest.json ./public/mix-manifest.json

RUN composer dump-autoload --optimize \
    && chown -R www-data:www-data storage bootstrap/cache modules

COPY docker/entrypoint.sh /usr/local/bin/akaunting-entrypoint
RUN chmod +x /usr/local/bin/akaunting-entrypoint

ENTRYPOINT ["akaunting-entrypoint"]
CMD ["apache2-foreground"]
