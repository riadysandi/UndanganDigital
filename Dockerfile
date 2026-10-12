# Stage 1: Build frontend assets
FROM node:20-alpine AS frontend
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .
RUN npm run build

# Stage 2: Production High-Performance Web Server (Nginx + PHP-FPM)
FROM php:8.3-fpm

# Install system dependencies, Nginx & PHP extensions
RUN apt-get update && apt-get install -y \
    nginx \
    unzip \
    libzip-dev \
    libsqlite3-dev \
    libpq-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    sqlite3 \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_sqlite pdo_pgsql pgsql zip bcmath gd opcache \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app
COPY . .

# Copy compiled frontend assets from Stage 1
COPY --from=frontend /app/public/build ./public/build

# Install PHP packages
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Configure Nginx
COPY docker/nginx.conf /etc/nginx/sites-available/default
RUN rm -f /etc/nginx/sites-enabled/default \
    && ln -s /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default \
    && mkdir -p /etc/nginx/conf.d \
    && cp docker/nginx.conf /etc/nginx/conf.d/default.conf

# Set up storage and database directories
RUN mkdir -p database storage/framework/sessions storage/framework/views storage/framework/cache storage/logs bootstrap/cache \
    && touch database/database.sqlite \
    && chmod -R 775 storage bootstrap/cache database \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod +x /app/docker-entrypoint.sh

EXPOSE 8080
ENV PORT=8080

CMD ["/app/docker-entrypoint.sh"]
