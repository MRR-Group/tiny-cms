FROM php:8.2-fpm-alpine

RUN apk add --no-cache $PHPIZE_DEPS git unzip icu-dev oniguruma-dev libpq-dev \
    && docker-php-ext-install intl pdo pdo_pgsql opcache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
