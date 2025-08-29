#!/usr/bin/env bash
echo "Running composer"
composer global require hirak/prestissimo

# Install all dependencies (including dev)
echo "Installing all dependencies..."
composer install --working-dir=/var/www/html

echo "Caching config..."
php artisan config:cache

echo "Caching routes..."
php artisan route:cache

echo "generating app key..."
php artisan key:generate --force

echo "Running migrations..."
php artisan migrate --force

echo "database seeder seeding super-admin user"
php artisan db:seed --force

echo "Removing dev dependencies..."
composer install --no-dev --working-dir=/var/www/html

echo 'Running app schedule'
php artisan schedule:run --no-interaction --quiet
sleep 60

echo "Running the queue ..."
php /var/www/artisan queue:work --verbose --tries=3 --timeout=180