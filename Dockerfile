# Stage 1: Build Stage
FROM php:8.4-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    bash \
    curl \
    libpng-dev \
    libzip-dev \
    zlib-dev \
    icu-dev \
    oniguruma-dev \
    postgresql-dev \
    nginx

# Install PHP extensions required by Laravel 12
RUN docker-php-ext-install pdo pdo_pgsql mbstring zip exif pcntl bcmath gd intl

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . .

# Install dependencies
RUN composer install --no-dev --optimize-autoloader

# Laravel setup and optimization
RUN php artisan storage:link && \
    php artisan optimize && \
    chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Setup Nginx
COPY ./docker/nginx/nginx.conf /etc/nginx/http.d/default.conf

# Configure PHP-FPM to log to stderr for Render
RUN echo "error_log = /dev/stderr" >> /usr/local/etc/php-fpm.d/docker.conf && \
    echo "catch_workers_output = yes" >> /usr/local/etc/php-fpm.d/docker.conf

EXPOSE 80

CMD ["sh", "-c", "nginx && php-fpm"]