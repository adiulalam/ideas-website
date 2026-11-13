# syntax=docker/dockerfile:1

# ---- PHP dependencies -------------------------------------------------------
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-progress --prefer-dist --no-scripts --no-autoloader --ignore-platform-reqs
COPY src/ src/
RUN composer dump-autoload --no-dev --optimize --classmap-authoritative

# ---- CSS (Tailwind) ---------------------------------------------------------
FROM node:22-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund
COPY resources/ resources/
COPY templates/ templates/
COPY public/assets/app.js public/assets/app.js
RUN npm run build:css

# ---- Runtime ----------------------------------------------------------------
FROM php:8.4-apache AS app

RUN docker-php-ext-install pdo_mysql opcache \
    && a2enmod headers \
    && a2dismod -f autoindex status \
    && rm -f /etc/apache2/conf-enabled/serve-cgi-bin.conf

COPY docker/php.ini "$PHP_INI_DIR/conf.d/zz-app.ini"
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf
COPY docker/entrypoint.sh /usr/local/bin/ideas-entrypoint
RUN sed -i 's/\r$//' /usr/local/bin/ideas-entrypoint && chmod 0755 /usr/local/bin/ideas-entrypoint

WORKDIR /var/www/html
COPY --from=vendor /app/vendor/ vendor/
COPY bin/ bin/
COPY database/migrations/ database/migrations/
COPY public/ public/
COPY src/ src/
COPY templates/ templates/
COPY --from=assets /app/public/assets/app.css public/assets/app.css

# Code is owned by root and read-only for Apache; only the upload directory is writable.
RUN chown -R root:root /var/www/html \
    && chmod -R a+rX,go-w /var/www/html \
    && chown www-data:www-data public/assets/img

HEALTHCHECK --interval=30s --timeout=10s --start-period=20s --retries=3 \
    CMD php -r 'exit(@file_get_contents("http://127.0.0.1/healthz") === false ? 1 : 0);'

ENTRYPOINT ["ideas-entrypoint"]
CMD ["apache2-foreground"]
