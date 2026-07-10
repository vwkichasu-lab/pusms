#!/usr/bin/env bash
set -e

if [ -d /data ]; then
    mkdir -p /data/database /data/storage/app/public

    if [ ! -L /var/www/html/storage/app/public ]; then
        rm -rf /var/www/html/storage/app/public
    fi

    ln -sfn /data/storage/app/public /var/www/html/storage/app/public
    chown -R www-data:www-data /data
fi

if [ "${DB_CONNECTION:-sqlite}" = "sqlite" ]; then
    mkdir -p "$(dirname "${DB_DATABASE:-/var/www/html/database/database.sqlite}")"
    touch "${DB_DATABASE:-/var/www/html/database/database.sqlite}"
    chown www-data:www-data "${DB_DATABASE:-/var/www/html/database/database.sqlite}"
fi

php artisan storage:link || true
php artisan migrate --force
php artisan db:seed --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

rm -f /etc/apache2/mods-enabled/mpm_event.* /etc/apache2/mods-enabled/mpm_worker.*
if [ ! -e /etc/apache2/mods-enabled/mpm_prefork.load ]; then
    a2enmod mpm_prefork
fi

APP_PORT="${PORT:-80}"
sed -i "s/Listen 80/Listen ${APP_PORT}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost *:${APP_PORT}>/" /etc/apache2/sites-available/000-default.conf

apache2-foreground
