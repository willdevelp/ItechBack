# Étape 1 : Image de base
FROM php:8.2-fpm

# Étape 2 : Installation des dépendances système
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

# Étape 5 : Copie du projet
COPY . .

# Étape 6 : Installation des dépendances Laravel
RUN composer install --optimize-autoloader

# Copier .env.example vers .env
RUN cp .env.example .env


# Générer la clé d'application Laravel
RUN php artisan key:generate


# Étape 7 : Droits sur les dossiers nécessaires
RUN chown -R www-data:www-data \
    /var/www/storage \
    /var/www/bootstrap/cache

# Étape 8 : Exposition du port FPM
EXPOSE 8080
# Étape 9 : Commande de démarrage
# CMD ["php", "artisan", "serve"]

# Commande de démarrage
CMD php artisan serve --host=0.0.0.0 --port=${PORT:-8080}