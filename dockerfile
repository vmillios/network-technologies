FROM php:8.2-fpm

RUN apt-get update && apt-get install -y \
    libonig-dev \
    libzip-dev \
    zip \
    unzip \
    default-mysql-client \
    && docker-php-ext-install pdo_mysql

# Copy app files
COPY ./app/ /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html