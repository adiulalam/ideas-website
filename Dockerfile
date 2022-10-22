FROM php:8.1.6-fpm

RUN docker-php-ext-install pdo pdo_mysql

WORKDIR /code
