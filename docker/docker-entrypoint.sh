#!/bin/sh
set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}Starting Docker Entrypoint for Laravel POS${NC}"

# Check if APP_KEY is set
if [ -z "$APP_KEY" ]; then
    echo -e "${YELLOW}APP_KEY not set, generating...${NC}"
    php artisan key:generate --force
    export APP_KEY=$(grep APP_KEY .env | cut -d '=' -f2)
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
php artisan config:cache
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

# Execute the main command
exec "$@"
