# Étape 1 : Build PHP avec extensions
FROM php:8.2-fpm as php

RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg-dev \
    libonig-dev \
    libxml2-dev \
    zip unzip curl git libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql mbstring exif pcntl bcmath gd

COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

COPY . .

RUN composer install --no-interaction --prefer-dist --optimize-autoloader \
    && chown -R www-data:www-data storage bootstrap/cache

# Étape 2 : Caddy + PHP
FROM caddy:2.7.4-alpine

COPY --from=php /usr/local /usr/local
COPY --from=php /var/www /var/www

WORKDIR /var/www

# Ajout de Caddyfile
COPY Caddyfile /etc/caddy/Caddyfile

# Expose HTTP port for Render
EXPOSE 80

CMD ["caddy", "run", "--config", "/etc/caddy/Caddyfile", "--adapter", "caddyfile"]
