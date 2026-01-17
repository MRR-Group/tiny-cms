FROM php:8.3-fpm-alpine

RUN apk add --no-cache \
    git \
    curl \
    libpq-dev \
    zip \
    unzip

ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions \
    pdo_pgsql \
    pcov

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

EXPOSE 9000
