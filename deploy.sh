#!/bin/bash

# Docker Deployment Script for Laravel POS
# This script helps with local Docker development and testing

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}======================================${NC}"
echo -e "${BLUE}  Laravel POS Docker Deployment${NC}"
echo -e "${BLUE}======================================${NC}"
echo ""

# Check if Docker is installed
if ! command -v docker &> /dev/null; then
    echo -e "${RED}Docker is not installed. Please install Docker first.${NC}"
    exit 1
fi

# Check if Docker Compose is installed
if ! command -v docker-compose &> /dev/null; then
    echo -e "${RED}Docker Compose is not installed. Please install Docker Compose first.${NC}"
    exit 1
fi

# Function to show menu
show_menu() {
    echo ""
    echo -e "${GREEN}Choose an option:${NC}"
    echo "1) Build and start services"
    echo "2) Start services"
    echo "3) Stop services"
    echo "4) Restart services"
    echo "5) View logs"
    echo "6) Enter application shell"
    echo "7) Install dependencies"
    echo "8) Run migrations"
    echo "9) Seed database"
    echo "10) Clear caches"
    echo "11) Optimize for production"
    echo "12) Clean everything (remove containers, volumes, images)"
    echo "13) Exit"
    echo ""
}

# Function to build and start
build_and_start() {
    echo -e "${YELLOW}Building Docker images...${NC}"
    docker-compose build --no-cache
    echo -e "${YELLOW}Starting services...${NC}"
    docker-compose up -d
    echo -e "${GREEN}✓ Services started!${NC}"
    echo -e "App: ${BLUE}http://localhost:8080${NC}"
    echo -e "phpMyAdmin: ${BLUE}http://localhost:8081${NC}"
}

# Function to start services
start_services() {
    echo -e "${YELLOW}Starting services...${NC}"
    docker-compose up -d
    echo -e "${GREEN}✓ Services started!${NC}"
}

# Function to stop services
stop_services() {
    echo -e "${YELLOW}Stopping services...${NC}"
    docker-compose down
    echo -e "${GREEN}✓ Services stopped${NC}"
}

# Function to restart services
restart_services() {
    echo -e "${YELLOW}Restarting services...${NC}"
    docker-compose restart
    echo -e "${GREEN}✓ Services restarted${NC}"
}

# Function to view logs
view_logs() {
    echo -e "${YELLOW}Showing logs (Ctrl+C to exit)...${NC}"
    docker-compose logs -f
}

# Function to enter shell
enter_shell() {
    echo -e "${YELLOW}Entering application shell...${NC}"
    docker-compose exec app bash
}

# Function to install dependencies
install_dependencies() {
    echo -e "${YELLOW}Installing PHP dependencies...${NC}"
    docker-compose exec app composer install
    echo -e "${YELLOW}Installing Node dependencies...${NC}"
    docker-compose exec app npm install
    echo -e "${YELLOW}Building frontend assets...${NC}"
    docker-compose exec app npm run build
    echo -e "${GREEN}✓ Dependencies installed${NC}"
}

# Function to run migrations
run_migrations() {
    echo -e "${YELLOW}Running migrations...${NC}"
    docker-compose exec app php artisan migrate
    echo -e "${GREEN}✓ Migrations completed${NC}"
}

# Function to seed database
seed_database() {
    echo -e "${YELLOW}Seeding database...${NC}"
    docker-compose exec app php artisan db:seed
    echo -e "${GREEN}✓ Database seeded${NC}"
}

# Function to clear caches
clear_caches() {
    echo -e "${YELLOW}Clearing caches...${NC}"
    docker-compose exec app php artisan config:clear
    docker-compose exec app php artisan cache:clear
    docker-compose exec app php artisan route:clear
    docker-compose exec app php artisan view:clear
    echo -e "${GREEN}✓ Caches cleared${NC}"
}

# Function to optimize
optimize_app() {
    echo -e "${YELLOW}Optimizing application...${NC}"
    docker-compose exec app php artisan config:cache
    docker-compose exec app php artisan route:cache
    docker-compose exec app php artisan view:cache
    docker-compose exec app php artisan event:cache
    echo -e "${GREEN}✓ Application optimized${NC}"
}

# Function to clean everything
clean_all() {
    echo -e "${RED}This will remove all containers, volumes, and images!${NC}"
    read -p "Are you sure? (y/N) " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        echo -e "${YELLOW}Cleaning up...${NC}"
        docker-compose down -v --rmi all --remove-orphans
        echo -e "${GREEN}✓ Cleanup completed${NC}"
    else
        echo -e "${YELLOW}Cancelled${NC}"
    fi
}

# Main loop
while true; do
    show_menu
    read -p "Enter option [1-13]: " choice

    case $choice in
        1) build_and_start ;;
        2) start_services ;;
        3) stop_services ;;
        4) restart_services ;;
        5) view_logs ;;
        6) enter_shell ;;
        7) install_dependencies ;;
        8) run_migrations ;;
        9) seed_database ;;
        10) clear_caches ;;
        11) optimize_app ;;
        12) clean_all ;;
        13)
            echo -e "${GREEN}Goodbye!${NC}"
            exit 0
            ;;
        *)
            echo -e "${RED}Invalid option. Please try again.${NC}"
            ;;
    esac
done
