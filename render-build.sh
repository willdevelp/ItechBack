#!/usr/bin/env bash
# render-build.sh

# Installer les dépendances
composer install --no-interaction --no-dev --prefer-dist

# Optimiser l'application
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
php artisan storage:link

# Migrer la base de données (optionnel - peut être fait manuellement)
# php artisan migrate --force
