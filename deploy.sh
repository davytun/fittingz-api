# deploy.sh
#!/bin/bash

echo "Deploying Fittingz API..."

# Pull latest code
git pull origin main

# Install dependencies
composer install --no-dev --optimize-autoloader

# Clear caches
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Cache config and routes
php artisan config:cache
php artisan route:cache

# Run migrations
php artisan migrate --force

# Optimize
php artisan optimize

# Set permissions
chmod -R 755 storage bootstrap/cache

echo "Deployment complete!"