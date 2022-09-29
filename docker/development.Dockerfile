FROM php:8.0-fpm

RUN apt-get update && apt-get install -y \
    libonig-dev \
    libzip-dev \
    libmcrypt-dev \
    libicu-dev \
    libpq-dev \
    unzip \
    bash \
    nano

RUN docker-php-ext-configure intl && \
    docker-php-ext-install \
    bcmath \
    mysqli \
    pdo \
    pdo_mysql \
    pdo_pgsql \
    gettext \
    zip \
    mbstring \
    intl

RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"
COPY ./php/uploads.ini "$PHP_INI_DIR/conf.d/uploads.ini"

WORKDIR /var/www/html

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN apt-get purge
RUN echo "alias ll='ls -la'" >> ~/.bashrc
