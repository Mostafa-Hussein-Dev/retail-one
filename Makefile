.PHONY: help build up down restart logs shell install migrate seed test clean

# Default target
help:
	@echo "Available commands:"
	@echo "  make build      - Build Docker images"
	@echo "  make up         - Start all services"
	@echo "  make down       - Stop all services"
	@echo "  make restart    - Restart all services"
	@echo "  make logs       - View logs from all services"
	@echo "  make shell      - Enter application container"
	@echo "  make install    - Install dependencies"
	@echo "  make migrate    - Run database migrations"
	@echo "  make seed       - Seed the database"
	@echo "  make test       - Run tests"
	@echo "  make clean      - Remove containers, volumes, and images"

# Build images
build:
	docker-compose build --no-cache

# Start services
up:
	docker-compose up -d

# Stop services
down:
	docker-compose down

# Restart services
restart:
	docker-compose restart

# View logs
logs:
	docker-compose logs -f

# Application logs
logs-app:
	docker-compose logs -f app

# Database logs
logs-db:
	docker-compose logs -f mysql

# Enter application shell
shell:
	docker-compose exec app bash

# Install dependencies
install:
	docker-compose exec app composer install
	docker-compose exec app npm install
	docker-compose exec app npm run build

# Run migrations
migrate:
	docker-compose exec app php artisan migrate

# Fresh migration with seed
migrate-fresh:
	docker-compose exec app php artisan migrate:fresh --seed

# Seed database
seed:
	docker-compose exec app php artisan db:seed

# Run tests
test:
	docker-compose exec app php artisan test

# Clear caches
clear:
	docker-compose exec app php artisan config:clear
	docker-compose exec app php artisan cache:clear
	docker-compose exec app php artisan route:clear
	docker-compose exec app php artisan view:clear

# Optimize for production
optimize:
	docker-compose exec app php artisan config:cache
	docker-compose exec app php artisan route:cache
	docker-compose exec app php artisan view:cache

# Link storage
storage-link:
	docker-compose exec app php artisan storage:link

# Generate application key
key-generate:
	docker-compose exec app php artisan key:generate

# Clean everything
clean:
	docker-compose down -v --rmi all --remove-orphans

# Rebuild and restart
rebuild: clean build up

# Production deployment check
prod-check:
	@echo "Checking production readiness..."
	@test -n "$(APP_KEY)" || (echo "❌ APP_KEY not set" && exit 1)
	@test -n "$(DB_DATABASE)" || (echo "❌ DB_DATABASE not set" && exit 1)
	@echo "✅ Production environment configured"
