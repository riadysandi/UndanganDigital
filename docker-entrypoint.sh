#!/bin/sh
set -e

echo "=== Initializing Undangan Digital SaaS on Railway ==="

# Ensure SQLite file exists
mkdir -p /app/database /app/storage/framework/sessions /app/storage/framework/views /app/storage/framework/cache /app/storage/logs /app/bootstrap/cache
touch /app/database/database.sqlite
chmod -R 775 /app/storage /app/bootstrap/cache /app/database

# Ensure storage link
php artisan storage:link --no-interaction || true

# Run database migrations and seeding
echo "Running migrations..."
php artisan migrate --force --no-interaction

echo "Running seeders..."
php artisan db:seed --force --no-interaction

# Optimize Laravel for production
echo "Caching configuration and routes..."
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

# Start server
PORT="${PORT:-8080}"
echo "Starting Undangan Digital server on port $PORT..."
exec php artisan serve --host=0.0.0.0 --port="$PORT"
