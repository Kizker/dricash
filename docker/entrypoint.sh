#!/bin/sh
set -e

# Ensure .env exists
if [ ! -f /var/www/html/.env ]; then
    if [ -f /var/www/html/.env.production ]; then
        echo "📋 Menggunakan .env.production sebagai .env..."
        cp /var/www/html/.env.production /var/www/html/.env
    elif [ -f /var/www/html/.env.example ]; then
        echo "📋 Menyalin .env.example sebagai .env..."
        cp /var/www/html/.env.example /var/www/html/.env
    fi
fi

# Set directory permissions
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Create storage symlink if missing
if [ ! -L /var/www/html/public/storage ]; then
    php artisan storage:link || true
fi

# Run cache optimization if in production
if [ "$APP_ENV" = "production" ] || grep -q "APP_ENV=production" /var/www/html/.env 2>/dev/null; then
    php artisan config:cache || true
    php artisan route:cache || true
    php artisan view:cache || true
fi

exec "$@"
