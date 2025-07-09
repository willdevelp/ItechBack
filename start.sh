#!/bin/sh
# Démarrer PHP-FPM en arrière-plan avec logs dans /tmp
php-fpm82 --nodaemonize --fpm-config /etc/php82/php-fpm.conf &

# Démarrer Caddy
exec /usr/bin/caddy run --config /etc/caddy/Caddyfile --adapter caddyfile
