#!/bin/sh

echo "Clearing and caching configuration..."
php artisan config:clear
php artisan config:cache

echo "Running database migrations..."
php artisan migrate --force

echo "Seeding database..."
php artisan db:seed --force

echo "Starting services..."
nginx && php-fpm
