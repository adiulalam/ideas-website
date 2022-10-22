FROM php:8.1.6-apache

RUN docker-php-ext-install pdo pdo_mysql

WORKDIR /code

RUN chmod 777 /tmp