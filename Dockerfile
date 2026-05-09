FROM php:8.2-cli

<<<<<<< HEAD
=======
# Instalar dependencias del sistema
>>>>>>> bf69975 (Fix login redirect)
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev

<<<<<<< HEAD
RUN docker-php-ext-install zip

RUN pecl install mongodb && docker-php-ext-enable mongodb

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY . /app

WORKDIR /app

RUN composer install
=======
# Instalar extensiones PHP
RUN docker-php-ext-install zip

# Instalar MongoDB extension
RUN pecl install mongodb && docker-php-ext-enable mongodb
>>>>>>> bf69975 (Fix login redirect)

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copiar proyecto
COPY . /app

WORKDIR /app

# Instalar dependencias PHP
RUN composer install

# Puerto de Render
EXPOSE 10000

# Ejecutar proyecto
CMD php -S 0.0.0.0:10000 -t public