#!/bin/sh
php-fpm82 -D && /usr/bin/caddy run --config /etc/caddy/Caddyfile --adapter caddyfile
