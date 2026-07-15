FROM php:8.4-fpm-alpine

# Set working directory
WORKDIR /var/www/html

# Install system dependencies, Nginx, and Supervisor
RUN apk update && apk add --no-cache \
    nginx \
    supervisor \
    bash \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    git

# Install PHP extensions helper and required extensions
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions \
    pdo_mysql \
    pdo_sqlite \
    sqlite3 \
    pdo_pgsql \
    pgsql \
    redis \
    zip \
    bcmath \
    opcache \
    gd \
    intl

# Configure PHP upload limits
RUN echo "upload_max_filesize=60M" > /usr/local/etc/php/conf.d/uploads.ini && \
    echo "post_max_size=60M" >> /usr/local/etc/php/conf.d/uploads.ini

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy configuration files
COPY nginx.conf /etc/nginx/http.d/default.conf
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh

# Ensure entrypoint is executable
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Copy application files (with correct permissions)
COPY --chown=www-data:www-data . /var/www/html

# Run composer install
# Note: Since .env might not exist yet, we run with --no-scripts to prevent Laravel hooks from failing
RUN composer install --no-interaction --no-plugins --no-scripts --prefer-dist

# Run composer autoload optimization
RUN composer dump-autoload --optimize --classmap-authoritative

# Ensure storage and cache directories exist and are writable
RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache && \
    chown -R www-data:www-data storage bootstrap/cache

# Expose backend port
EXPOSE 8000

# Set entrypoint
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]

# Start Supervisor to run both Nginx and PHP-FPM
CMD ["/usr/bin/supervisord", "-n", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
