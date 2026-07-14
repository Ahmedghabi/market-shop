FROM dunglas/frankenphp:1-php8.4-alpine

WORKDIR /app

RUN apk add --no-cache git icu-dev libzip-dev postgresql-dev \
    && install-php-extensions intl opcache pcov pdo_pgsql redis zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY etc/docker/frankenphp/Caddyfile /etc/caddy/Caddyfile
