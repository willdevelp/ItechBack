# Dockerfile pour Laravel sur Render
FROM php:8.2-fpm-alpine

# Installer les dépendances système (PostgreSQL, etc.)
RUN apk add --no-cache \
    nginx \
    postgresql-dev \
    && docker-php-ext-install pdo pdo_pgsql

# Installer Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copier l'application
WORKDIR /var/www/html
COPY . .

# Installer les dépendances PHP
RUN composer install --no-dev --optimize-autoloader

# Configurer Nginx et PHP-FPM (si nécessaire)
COPY deploy/nginx.conf /etc/nginx/nginx.conf

# Exposer le port 8000 (pour php artisan serve)
EXPOSE 8000

# Lancer l'application
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
