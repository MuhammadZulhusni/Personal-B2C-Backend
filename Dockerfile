FROM php:8.2-cli-alpine

# Install only essential packages
RUN apk add --no-cache \
    git \
    curl \
    sqlite \
    sqlite-dev \
    libpng-dev \
    oniguruma-dev \
    libxml2-dev \
    zip \
    unzip

# Install only necessary PHP extensions
RUN docker-php-ext-install pdo pdo_mysql pdo_sqlite mbstring bcmath

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copy application
COPY . .

# Install dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Setup storage
RUN mkdir -p /var/data && \
    touch /var/data/database.sqlite && \
    chmod -R 777 /var/data storage bootstrap/cache

EXPOSE 8000

CMD php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=${PORT:-8000}