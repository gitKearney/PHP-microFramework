FROM php:8.2-fpm

RUN pecl install xdebug-3.3.0 \
    && docker-php-ext-enable xdebug

