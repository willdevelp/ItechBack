# Étape 1 : Build PHP
FROM php:8.2-fpm as php

RUN apt-get update && apt-get install -y \
    libpng-dev libjpeg-dev libonig-dev libxml2-dev libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql mbstring exif gd

COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www
COPY . .
RUN composer install --no-dev --optimize-autoloader \
    && chown -R www-data:www-data storage bootstrap/cache

# Étape 2 : Image finale
FROM caddy:2.7.4-alpine

# Installer PHP-FPM avec permissions correctes
RUN apk add --no-cache php82-fpm php82-pdo_pgsql && \
    mkdir -p /var/log/php82 && \
    chown -R 1000:1000 /var/log/php82 /var/lib/php82

# Copier l'application et la config PHP
COPY --from=php /var/www /var/www
COPY --from=php /usr/local/etc/php/conf.d/ /usr/local/etc/php/conf.d/

# Configurer Caddy
COPY Caddyfile /etc/caddy/Caddyfile
RUN chown -R 1000:1000 /etc/caddy

# Script de démarrage personnalisé
COPY start.sh /start.sh
RUN chmod +x /start.sh

USER 1000

EXPOSE 80
CMD ["/start.sh"]
