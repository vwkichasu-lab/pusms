FROM php:8.3-apache

WORKDIR /var/www/html

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        unzip \
        libzip-dev \
        libpng-dev \
        libsqlite3-dev \
        libonig-dev \
        libxml2-dev \
        curl \
        nodejs \
        npm \
    && docker-php-ext-install pdo pdo_mysql pdo_sqlite zip mbstring exif pcntl bcmath gd \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY . .
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf
COPY docker/start.sh /usr/local/bin/start-pusms

RUN composer install --no-dev --optimize-autoloader --no-interaction \
    && npm ci \
    && npm run build \
    && chmod +x /usr/local/bin/start-pusms \
    && chown -R www-data:www-data storage bootstrap/cache database

EXPOSE 80

CMD ["start-pusms"]
