FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    zip unzip curl libzip-dev libpng-dev libonig-dev libxml2-dev \
    sqlite3 libsqlite3-dev \
    && docker-php-ext-install pdo_mysql zip pdo_sqlite

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/project-A