#!/bin/sh
# Configurer PHP-FPM pour utiliser /tmp
export PHP_FPM_DIR=/tmp/php-fpm
mkdir -p $PHP_FPM_DIR

# Démarrer PHP-FPM avec configuration personnalisée
php-fpm82 --nodaemonize --fpm-config /etc/php82/php-fpm.conf &

# Démarrer Caddy
exec /usr/bin/caddy run --config /etc/caddy/Caddyfile --adapter caddyfile