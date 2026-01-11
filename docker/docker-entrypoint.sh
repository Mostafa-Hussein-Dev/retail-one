#!/bin/sh
set -e

# Set default PORT if not provided (Render provides this)
if [ -z "${PORT}" ]; then
  export PORT=10000
fi

# Render nginx template with envsubst using $PORT
if [ -f /etc/nginx/http.d/default.conf.template ]; then
  envsubst '${PORT}' < /etc/nginx/http.d/default.conf.template > /etc/nginx/http.d/default.conf
else
  echo "Missing /etc/nginx/http.d/default.conf.template"
  exit 1
fi

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}Starting Docker Entrypoint for Laravel POS${NC}"

# Fail fast if APP_KEY is missing (DO NOT auto-generate)
if [ -z "${APP_KEY}" ]; then
  echo "APP_KEY is not set. Configure APP_KEY in Render environment variables."
  exit 1
fi

# Create necessary directories
echo -e "${GREEN}Creating storage directories...${NC}"
mkdir -p /var/www/html/storage/framework/cache
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/bootstrap/cache

# Set proper permissions
echo -e "${GREEN}Setting permissions...${NC}"
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Clear and cache config
echo -e "${GREEN}Optimizing application...${NC}"
php artisan route:cache
php artisan view:cache

# Run migrations if DB_CONNECTION is set and not in local environment
if [ ! -z "$DB_CONNECTION" ] && [ "$APP_ENV" != "local" ]; then
    echo -e "${GREEN}Running database migrations...${NC}"

    # Wait for database to be ready (with timeout)
    if [ ! -z "$DB_HOST" ]; then
        echo -e "${YELLOW}Waiting for database connection...${NC}"
        timeout=30
        while [ $timeout -gt 0 ]; do
            if php artisan db:show 2>/dev/null; then
                echo -e "${GREEN}Database is ready!${NC}"
                break
            fi
            sleep 1
            timeout=$((timeout - 1))
        done

        if [ $timeout -le 0 ]; then
            echo -e "${RED}Database connection timeout${NC}"
        fi
    fi

    # Run migrations
    php artisan migrate --force

    # Optionally seed the database (uncomment if needed)
    # php artisan db:seed --force
fi

# Link storage if not exists
if [ ! -L /var/www/html/public/storage ]; then
    echo -e "${GREEN}Creating storage symlink...${NC}"
    php artisan storage:link
fi

echo -e "${GREEN}Entrypoint complete. Starting application...${NC}"

# Execute supervisord
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
