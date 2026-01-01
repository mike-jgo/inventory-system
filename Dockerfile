FROM serversideup/php:8.4-fpm-nginx

# 1. Set environment variables
ENV WEBROOT /var/www/html/public
ENV APP_ENV production
ENV APP_DEBUG false
ENV LOG_CHANNEL stderr

# Enable the automated script runner
ENV RUN_SCRIPTS 1

# 2. Copy your application files and set ownership to www-data
COPY --chown=www-data:www-data . /var/www/html

# 3. Move your deploy script to the location the image expects
# We use 'mkdir -p' to ensure the directory exists before moving
RUN mkdir -p /var/www/html/.docker && \
    mv /var/www/html/scripts/00-laravel-deploy.sh /var/www/html/.docker/run.sh && \
    chmod +x /var/www/html/.docker/run.sh