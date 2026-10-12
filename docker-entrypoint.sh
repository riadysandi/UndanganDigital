#!/bin/sh
set -e

echo "=== Initializing Undangan Digital SaaS on Railway ==="

# Ensure storage directories
mkdir -p /app/storage/framework/sessions /app/storage/framework/views /app/storage/framework/cache /app/storage/logs /app/bootstrap/cache
chmod -R 775 /app/storage /app/bootstrap/cache
chown -R www-data:www-data /app/storage /app/bootstrap/cache

# If SQLite fallback is active (when DATABASE_URL is not set)
if [ "$DB_CONNECTION" = "sqlite" ] || [ -z "$DATABASE_URL" -a -z "$PGHOST" ]; then
    echo "Using SQLite database..."
    mkdir -p /app/database
    touch /app/database/database.sqlite
    chmod -R 775 /app/database
    chown -R www-data:www-data /app/database
else
    echo "Using Managed PostgreSQL Database..."
fi

# Ensure storage link
php artisan storage:link --no-interaction || true

# Run database migrations
echo "Running migrations..."
php artisan migrate --force --no-interaction

# Run idempotent seeders (firstOrCreate safe)
echo "Running seeders..."
php artisan db:seed --force --no-interaction || true

# Optimize Laravel for production
echo "Caching configuration and routes..."
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

# Configure Nginx port from Railway environment variable $PORT
PORT="${PORT:-8080}"
echo "Configuring Nginx on port $PORT..."
sed -i "s/listen [0-9]*;/listen $PORT;/" /etc/nginx/sites-available/default 2>/dev/null || true
sed -i "s/listen [0-9]*;/listen $PORT;/" /etc/nginx/conf.d/default.conf 2>/dev/null || true

# Start PHP-FPM in background
echo "Starting PHP-FPM pool..."
php-fpm -D

# Start Nginx in foreground
echo "Starting Nginx production web server on port $PORT..."
exec nginx -g "daemon off;"
