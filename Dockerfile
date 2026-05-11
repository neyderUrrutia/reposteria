FROM php:8.2-cli

# Instalar dependencias del sistema
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev

# Instalar extensiones PHP
RUN docker-php-ext-install zip

# Instalar extensión MongoDB
RUN pecl install mongodb && docker-php-ext-enable mongodb

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