#!/bin/sh
# Créer le répertoire de logs avec les bonnes permissions
mkdir -p /var/log/php82 && chown 1000:1000 /var/log/php82

# Démarrer PHP-FPM en arrière-plan avec config personnalisée
php-fpm82 --fpm-config /home/1000/.php-fpm.conf -D

# Démarrer Caddy
exec /usr/bin/caddy run --config /etc/caddy/Caddyfile --adapter caddyfile
