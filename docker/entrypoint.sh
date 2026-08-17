#!/bin/sh
set -e

# Ensure storage directories exist and have proper permissions
mkdir -p /var/www/html/storage/framework/cache/data \
         /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/logs \
         /var/www/html/bootstrap/cache

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Create storage symbolic link
php artisan storage:link --force || true

# Run database migrations if DB is configured
if [ -n "$DB_HOST" ] || [ -n "$DATABASE_URL" ]; then
    echo "Running database migrations..."
    php artisan migrate --force || true
fi

# Optimize Laravel cache for production
echo "Caching Laravel configuration and routes..."
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

# Start Supervisor (which starts Nginx and PHP-FPM)
echo "Starting web server..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
