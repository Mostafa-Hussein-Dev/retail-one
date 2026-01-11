# Dockerfile for Laravel POS on Render
# Multi-stage build for optimized image size

# Stage 1: Dependencies
FROM composer:2.8 AS vendor
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --prefer-dist --optimize-autoloader --no-interaction

# Stage 2: Frontend Build
FROM node:22-alpine AS frontend
WORKDIR /app
COPY package.json package-lock.json* ./
RUN npm ci
COPY . .
RUN npm run build

# Stage 3: Application
FROM php:8.2-fpm-alpine AS application

# Install system dependencies
RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    zip \
    unzip \
    git \
    oniguruma-dev \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    bcmath \
    exif \
    gd \
    mbstring \
    pdo \
    pdo_mysql \
    zip

# Install Redis extension (optional, for caching/sessions)
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apk del .build-deps

# Create application directory
WORKDIR /var/www/html

# Copy application files
COPY . .

# Copy vendor dependencies from Stage 1
COPY --from=vendor /app/vendor/ ./vendor/

# Copy built frontend assets from Stage 2
COPY --from=frontend /app/public/build ./public/build

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Copy configuration files
COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh

RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Expose port 80 (Render will route to this)
EXPOSE 80

# Set environment variables for Laravel
ENV APP_ENV=production
ENV APP_DEBUG=false
ENV APP_KEY=${APP_KEY}
ENV DB_CONNECTION=mysql
ENV DB_HOST=${DB_HOST}
ENV DB_PORT=${DB_PORT:-3306}
ENV DB_DATABASE=${DB_DATABASE}
ENV DB_USERNAME=${DB_USERNAME}
ENV DB_PASSWORD=${DB_PASSWORD}
ENV CACHE_DRIVER=redis
ENV SESSION_DRIVER=redis
ENV QUEUE_CONNECTION=redis

# Health check for Render
HEALTHCHECK --interval=30s --timeout=10s --start-period=60s --retries=3 \
    CMD curl -f http://localhost/ || exit 1

# Start supervisord to run both PHP-FPM and Nginx
ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["supervisord", "-n", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
