FROM serversideup/php:8.4-fpm-nginx

COPY . .

# Copy deployment script to entrypoint directory (runs on container start)
RUN cp scripts/00-laravel-deploy.sh /etc/entrypoint.d/00-laravel-deploy.sh && \
    chmod +x /etc/entrypoint.d/00-laravel-deploy.sh

# Laravel config
ENV APP_ENV production
ENV APP_DEBUG false
ENV LOG_CHANNEL stderr

# Allow composer to run as root
ENV COMPOSER_ALLOW_SUPERUSER 1
