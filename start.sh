#!/bin/sh
php-fpm82 -D && caddy run --config /etc/caddy/Caddyfile --adapter caddyfile
