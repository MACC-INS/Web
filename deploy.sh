#!/bin/bash

# Secure deployment script for flights application

set -e  # Exit on error

echo "Starting secure deployment..."

# Validate environment variables
if [ -z "$DB_ROOT_PASSWORD" ] || [ -z "$DB_APP_PASSWORD" ]; then
    echo "Error: DB_ROOT_PASSWORD and DB_APP_PASSWORD must be set"
    echo "Run: export DB_ROOT_PASSWORD='password' && export DB_APP_PASSWORD='password'"
    exit 1
fi

# Create secure directories
mkdir -p nginx/logs php/tmp
chmod 755 nginx/logs php/tmp

# Set strict permissions
find . -type f -name "*.php" -exec chmod 644 {} \;
find . -type f -name "*.sql" -exec chmod 600 {} \;
find . -type f -name "*.sh" -exec chmod 700 {} \;

# Stop existing containers
docker compose down

# Build with security
docker compose build --no-cache --pull

# Start with environment variables
DB_ROOT_PASSWORD="$DB_ROOT_PASSWORD" \
DB_APP_PASSWORD="$DB_APP_PASSWORD" \
MYSQL_DATABASE="${MYSQL_DATABASE:-flights}" \
MYSQL_USER="${MYSQL_USER:-app_user}" \
docker compose up -d

# Wait for services
echo "Waiting for services to be ready..."
sleep 30

# Basic security checks
echo "Running security checks..."
docker exec flights-php php -r "if (!extension_loaded('mysqli')) { echo 'MySQLi not loaded!'; exit(1); }" && echo "✓ PHP MySQLi: OK"
docker exec flights-db mysql -u app_user -p"$DB_APP_PASSWORD" -e "SHOW DATABASES;" flights && echo "✓ Database: OK"

# Apply resource limits
docker update --memory=512m --memory-swap=1g flights-php 2>/dev/null || true
docker update --memory=1g --memory-swap=2g flights-db 2>/dev/null || true
docker update --memory=256m --memory-swap=512m flights-nginx 2>/dev/null || true

echo "Deployment completed successfully!"
docker compose ps