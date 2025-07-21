# Étape 1 : Image de base PHP
FROM php:8.2-fpm

# Étape 2 : Dépendances système
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    curl \
    git \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql mbstring exif pcntl bcmath gd

# Étape 3 : Installation de Composer
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

# Étape 4 : Création du dossier de travail
WORKDIR /var/www

# Étape 5 : Copie du code source Laravel
COPY . .

# Étape 6 : Copie et configuration initiale
RUN cp .env.example .env

# Étape 7 : Génération de la clé Laravel (avant composer install)
RUN php artisan key:generate

# Étape 8 : Installation des dépendances Laravel
RUN composer install --optimize-autoloader --no-interaction --prefer-dist

# Étape 9 : Création du lien storage
RUN php artisan storage:link

# Étape 10 : Permissions
RUN chown -R www-data:www-data \
    /var/www/storage \
    /var/www/bootstrap/cache

# Étape 11 : Exposition du port
EXPOSE 8080

# Étape 12 : Commande de démarrage
CMD php artisan serve --host=0.0.0.0 --port=${PORT:-8080}