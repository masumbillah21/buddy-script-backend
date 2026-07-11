FROM php:8.3-fpm-alpine

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
    zip \
    bcmath \
    opcache \
    gd \
    intl

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

# Run composer install (optimized for production)
# Note: Since .env might not exist yet, we run with --no-scripts to prevent Laravel hooks from failing
RUN composer install --no-dev --no-interaction --no-plugins --no-scripts --prefer-dist

# Run composer autoload optimization
RUN composer dump-autoload --optimize --no-dev --classmap-authoritative

# Ensure storage and cache directories exist and are writable
RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache && \
    chown -R www-data:www-data storage bootstrap/cache

# Expose backend port
EXPOSE 8000

# Set entrypoint
ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]

# Start Supervisor to run both Nginx and PHP-FPM
CMD ["/usr/bin/supervisord", "-n", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
