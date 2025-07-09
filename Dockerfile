FROM caddy:2.7.4-alpine

# Installer PHP-FPM directement
RUN apk add --no-cache php82-fpm php82-pdo_pgsql

COPY --from=php /var/www /var/www
COPY Caddyfile /etc/caddy/Caddyfile

# Script de démarrage
COPY start.sh /start.sh
RUN chmod +x /start.sh

EXPOSE 80

CMD ["/start.sh"]
