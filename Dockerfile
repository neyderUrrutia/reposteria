FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev

RUN docker-php-ext-install zip

RUN pecl install mongodb && docker-php-ext-enable mongodb

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY . /app

WORKDIR /app

RUN composer install

EXPOSE 10000

CMD php -S 0.0.0.0:10000 -t public
