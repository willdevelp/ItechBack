# Étape 1 : Builder PHP (depuis Windows, pas de problème de permissions ici)
FROM php:8.2-fpm as php_builder

# Installer les dépendances Linux (mais construit depuis Windows)
RUN apt-get update && apt-get install -y \
    libpng-dev libjpeg-dev libonig-dev libxml2-dev libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql mbstring exif gd

# Installer Composer
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www
COPY . .
RUN composer install --no-dev --optimize-autoloader \
    && chmod -R 755 storage bootstrap/cache

# Étape 2 : Image finale basée sur Alpine (pour Render)
FROM caddy:2.7.4-alpine

# Installer PHP-FPM depuis les dépôts Alpine (compatible Windows -> Linux container)
RUN apk add --no-cache \
    php82 \
    php82-fpm \
    php82-pdo \
    php82-pdo_pgsql \
    php82-mbstring \
    php82-exif \
    php82-gd \
    --repository=https://dl-cdn.alpinelinux.org/alpine/v3.18/community/

# Configurer les permissions pour Render (UID 1000)
RUN mkdir -p /tmp/php-fpm \
    && chown -R 1000:1000 /tmp/php-fpm /var/www \
    && chmod -R 755 /var/www

# Copier l'application depuis le builder
COPY --from=php_builder /var/www /var/www

# Copier la config Caddy
COPY Caddyfile /etc/caddy/Caddyfile
RUN chmod 644 /etc/caddy/Caddyfile

# Script de démarrage (adapté pour Render)
COPY start.sh /start.sh
RUN chmod +x /start.sh

# Utilisateur non-root (obligatoire sur Render)
USER 1000

EXPOSE 80
CMD ["/start.sh"]
