# Étape 1 : Builder PHP
FROM php:8.2-fpm as php_builder

RUN apt-get update && apt-get install -y \
    libpng-dev libjpeg-dev libonig-dev libxml2-dev libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql mbstring exif gd

COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www
COPY . .
RUN composer install --no-dev --optimize-autoloader \
    && find storage bootstrap/cache -type d -exec chmod 775 {} \;

# Étape 2 : Image finale
FROM caddy:2.7.4-alpine

# Solution clé : installer en mode root puis passer à l'utilisateur 1000
RUN apk add --no-cache \
    php82 \
    php82-fpm \
    php82-pdo \
    php82-pdo_pgsql \
    php82-mbstring \
    php82-exif \
    php82-gd \
    --repository=https://dl-cdn.alpinelinux.org/alpine/v3.18/community/ \
    && mkdir -p /tmp/php-fpm \
    && chmod -R 777 /tmp/php-fpm

# Copier l'application (pas besoin de chown)
COPY --from=php_builder /var/www /var/www
RUN chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Configurer Caddy
COPY Caddyfile /etc/caddy/Caddyfile
RUN chmod 644 /etc/caddy/Caddyfile

# Script de démarrage
COPY start.sh /start.sh
RUN chmod +x /start.sh

# Solution Render : utiliser l'UID 1000 sans chown
USER 1000

EXPOSE 80
CMD ["/start.sh"]
