FROM php:8.3-fpm-alpine

# Arguments for user/group IDs
ARG USER_ID=1000
ARG GROUP_ID=1000

RUN apk add --no-cache \
    git \
    curl \
    libpq-dev \
    zip \
    unzip \
    shadow

ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions \
    pdo_pgsql \
    pcov

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create user with matching host UID/GID
RUN if getent passwd ${USER_ID} > /dev/null; then \
        USER_NAME=$(getent passwd ${USER_ID} | cut -d: -f1); \
        deluser $USER_NAME; \
    fi && \
    if getent group ${GROUP_ID} > /dev/null; then \
        GROUP_NAME=$(getent group ${GROUP_ID} | cut -d: -f1); \
        delgroup $GROUP_NAME; \
    fi && \
    addgroup -g ${GROUP_ID} appuser && \
    adduser -D -u ${USER_ID} -G appuser appuser

WORKDIR /var/www/html

USER appuser

EXPOSE 9000
