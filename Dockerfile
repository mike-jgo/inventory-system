# Build vendor dependencies
FROM composer:2.7 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress --optimize-autoloader
COPY . .
RUN composer dump-autoload --optimize --no-dev

# Build frontend assets
FROM node:20 AS frontend
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci --no-progress
COPY . ./
RUN npm run build

# Final runtime image
FROM ghcr.io/render-examples/php-fpm-nginx:latest
WORKDIR /var/www/html

ENV APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stderr \
    PORT=8080

COPY --from=vendor /app /var/www/html
COPY --from=frontend /app/public/build /var/www/html/public/build

COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY docker/php/php-fpm.conf /usr/local/etc/php-fpm.d/zz-docker.conf

RUN mkdir -p /run/php \
    && chown -R www-data:www-data /run/php storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

EXPOSE 8080

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
