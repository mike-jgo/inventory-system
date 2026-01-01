#!/bin/sh

echo "Running database migrations..."
php artisan migrate --force

echo "Starting services..."
nginx && php-fpm
