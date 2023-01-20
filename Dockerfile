ARG PHP_VERSION=8.1

FROM ubuntu:22.04 as build-php

ARG DEBIAN_FRONTEND=noninteractive # Disable prompt of apt
RUN apt update && apt install -y --no-install-recommends \
    curl                        \
    unzip                       \
    ca-certificates             \
    php${PHP_VERSION}           \
    php${PHP_VERSION}-xml       \
    php${PHP_VERSION}-curl      \
    php${PHP_VERSION}-mbstring

RUN curl -sS https://getcomposer.org/installer -o /tmp/composer-setup.php
RUN php /tmp/composer-setup.php --install-dir=/usr/bin --filename=composer


WORKDIR /home/app

COPY . /home/app/
RUN chown -R www-data:www-data /home/app
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN composer install --no-dev --prefer-dist --optimize-autoloader --ignore-platform-reqs
# RUN php artisan migrate

FROM php:${PHP_VERSION}-fpm

RUN docker-php-ext-install pdo_mysql
RUN docker-php-ext-enable pdo_mysql

COPY --from=build-php /home/app /var/www/html
