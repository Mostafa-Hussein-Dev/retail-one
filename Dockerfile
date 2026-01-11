# ======================================================
# Stage 1: PHP Dependencies (Composer)
# ======================================================
FROM composer:2.8 AS vendor

# Install system deps required for PHP extensions
RUN apk add --no-cache \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    libzip-dev \
    oniguruma-dev \
    $PHPIZE_DEPS

# Build required PHP extensions for Composer platform checks
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install gd zip

WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction


# ======================================================
# Stage 2: Frontend Build (Vite / npm)
# ======================================================
FROM node:22-alpine AS frontend

WORKDIR /app
COPY package.json package-lock.json* ./
RUN npm ci

COPY . .
RUN npm run build


# ======================================================
# Stage 3: Application Runtime (PHP + nginx)
# ======================================================
FROM php:8.2-fpm-alpine AS application

# Install system packages
RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    zip \
    unzip \
    git \
    gettext \
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
    pdo_pgsql \
    zip

# (Optional) Redis extension
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
 && pecl install redis \
 && docker-php-ext-enable redis \
 && apk del .build-deps

# App directory
WORKDIR /var/www/html

# Copy application source
COPY . .

# Copy PHP dependencies
COPY --from=vendor /app/vendor ./vendor

# Copy built frontend assets
COPY --from=frontend /app/public/build ./public/build

# Permissions
RUN chown -R www-data:www-data /var/www/html \
 && chmod -R 755 /var/www/html/storage \
 && chmod -R 755 /var/www/html/bootstrap/cache

# nginx + supervisor config
COPY docker/nginx.conf.template /etc/nginx/http.d/default.conf.template
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Render ignores EXPOSE, but keep for clarity
EXPOSE 10000

# Health check
HEALTHCHECK --interval=30s --timeout=10s --start-period=60s --retries=3 \
  CMD curl -f http://localhost:${PORT:-10000}/ || exit 1

ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["supervisord", "-n", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
