#!/bin/sh

echo "Running database migrations..."
php artisan migrate --force

echo "Seeding database..."
php artisan db:seed --force

echo "Starting services..."
nginx && php-fpm
