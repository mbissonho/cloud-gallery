# =============================================================================
# Stage 1: build — install PHP extensions and vendor/ via Composer
# =============================================================================
FROM php:8.4-fpm-alpine AS build

WORKDIR /var/www/html

RUN apk add --no-cache \
    git \
    unzip \
    libzip-dev \
    mariadb-connector-c-dev \
    autoconf \
    build-base \
    make \
    g++

RUN docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_mysql \
        zip \
        bcmath \
        opcache \
    && pecl install redis \
    && docker-php-ext-enable redis

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY --chown=www-data:www-data . .

RUN sed 's_@php artisan package:discover_/bin/true_;' -i composer.json \
    && composer install \
        --ignore-platform-req=php \
        --no-dev \
        --optimize-autoloader \
        --no-interaction \
        --no-progress \
    && composer clear-cache \
    && chown -R www-data:www-data /var/www/html

# =============================================================================
# Stage 2: production — minimal runtime image (no composer, no build tools)
# =============================================================================
FROM php:8.4-fpm-alpine AS production

WORKDIR /var/www/html

RUN apk add --no-cache \
    libzip \
    mariadb-connector-c

COPY --from=build /usr/local/lib/php/extensions/ /usr/local/lib/php/extensions/
COPY --from=build /usr/local/etc/php/conf.d/ /usr/local/etc/php/conf.d/

COPY --from=build --chown=www-data:www-data /var/www/html /var/www/html

COPY ./.production/app/bin/deploy /usr/local/bin/deploy
COPY ./.production/app/bin/init_migration /usr/local/bin/init_migration
COPY ./.production/app/bin/init_storage /usr/local/bin/init_storage
RUN chmod +x /usr/local/bin/deploy /usr/local/bin/init_migration /usr/local/bin/init_storage

ENTRYPOINT ["deploy"]

EXPOSE 9000

CMD ["php-fpm"]
