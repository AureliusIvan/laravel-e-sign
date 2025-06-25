#!/bin/bash
set -e

# Create required directories if they don't exist
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/storage/framework/cache
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/bootstrap/cache

# Set proper permissions
chown -R www-data:www-data /var/www/html/storage
chown -R www-data:www-data /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage
chmod -R 775 /var/www/html/bootstrap/cache

# Fixing Laravel environment paths
php artisan config:clear
php artisan view:clear
#php artisan cache:clear

# Execute the main command
exec "$@"
