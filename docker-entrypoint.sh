#!/bin/sh
set -e

# Copy env if not exists
if [ ! -f .env ]; then
    echo "Creating .env file..."
    cp .env .env
fi

# Generate app key if not set
if ! grep -q "APP_KEY=base64:" .env || [ -z "$(grep APP_KEY .env | cut -d '=' -f 2)" ]; then
    echo "Generating Application Key..."
    php artisan key:generate --force
fi

# Handle SQLite database creation if configured
DB_CONN=$(grep '^DB_CONNECTION=' .env | cut -d '=' -f 2)
if [ "$DB_CONN" = "sqlite" ] || [ -z "$DB_CONN" ]; then
    DB_DATABASE=$(grep '^DB_DATABASE=' .env | cut -d '=' -f 2)
    if [ -z "$DB_DATABASE" ]; then
        DB_DATABASE="database/database.sqlite"
    fi
    
    # If path starts with absolute root, use it directly, otherwise touch relative to current directory
    if [ ! -f "$DB_DATABASE" ]; then
        echo "Creating SQLite database at $DB_DATABASE..."
        mkdir -p "$(dirname "$DB_DATABASE")"
        touch "$DB_DATABASE"
    fi
    chown -R www-data:www-data "$(dirname "$DB_DATABASE")"
fi

# Cache config/routes/views for production performance
if [ "$APP_ENV" = "production" ]; then
    echo "Caching configurations..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
fi

# Run database migrations
echo "Running migrations..."
php artisan migrate --force

# Seed database if requested via SEED_DATABASE env variable
if [ "$SEED_DATABASE" = "true" ]; then
    echo "Seeding database..."
    php artisan db:seed --force
fi

# Generate Swagger API documentation
echo "Generating Swagger documentation..."
php artisan l5-swagger:generate

# Correct folder permissions for Laravel storage and bootstrap cache
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Execute container CMD
exec "$@"
