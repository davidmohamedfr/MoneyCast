#!/bin/sh
set -e

echo "🚀 Starting Laravel development container..."

# Generate Wayfinder types for Vite
echo "📝 Generating Wayfinder types..."
php artisan wayfinder:generate --with-form

echo "✅ Wayfinder types generated"

# Create and set permissions for Xdebug log
touch /tmp/xdebug.log
chown www-data:www-data /tmp/xdebug.log
chmod 664 /tmp/xdebug.log

echo "🎯 Starting PHP-FPM..."

# Start PHP-FPM
exec php-fpm
