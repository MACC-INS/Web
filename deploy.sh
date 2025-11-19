#!/bin/bash

# Deployment script for flights application

set -e  # Exit on error

echo "Starting secure deployment..."

# Load environment variables
if [ -f .env ]; then
    source .env
else
    echo "Error: .env file not found"
    exit 1
fi

# Validate required environment variables
for var in DB_ROOT_PASSWORD DB_APP_PASSWORD; do
    if [ -z "${!var}" ]; then
        echo "Error: $var is not set in .env file"
        exit 1
    fi
done

# Create necessary directories
mkdir -p nginx/logs php/tmp
chmod 755 nginx/logs php/tmp

# Set proper permissions
find . -type f -name "*.php" -exec chmod 644 {} \;
find . -type f -name "*.sql" -exec chmod 600 {} \;
chmod 600 .env

# Stop existing containers
docker-compose down

# Build and start containers
docker-compose up -d --build

# Wait for services to be healthy
echo "Waiting for services to be ready..."
sleep 30

# Run security checks
echo "Running security checks..."
docker exec flights-php php -r "if (!extension_loaded('mysqli')) { echo 'MySQLi not loaded!'; exit(1); }"
docker exec flights-db mysql -u app_user -p${DB_APP_PASSWORD} -e "SHOW DATABASES;" flights

echo "Deployment completed successfully!"
echo "Application should be available at http://your-server-ip"

# Show container status
docker-compose ps