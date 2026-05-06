FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    libzip-dev \
    unzip \
    && docker-php-ext-install pdo pdo_sqlite zip

COPY . /var/www/html

RUN chown -R www-data:www-data /var/www/html

EXPOSE 9000
