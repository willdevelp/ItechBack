FROM caddy:2.7.4-alpine

RUN apk add --no-cache php82-fpm php82-pdo_pgsql

COPY --from=php /var/www /var/www
COPY Caddyfile /etc/caddy/Caddyfile

# Script corrigé avec permissions
COPY start.sh /start.sh
RUN chown 1000:1000 /start.sh && \
    chmod +x /start.sh && \
    chmod +x /usr/bin/caddy

USER 1000

EXPOSE 80

CMD ["/start.sh"]
