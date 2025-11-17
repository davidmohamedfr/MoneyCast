#!/bin/sh
set -e

echo "🚀 Starting Laravel development container..."

# Generate Wayfinder types for Vite
echo "📝 Generating Wayfinder types..."
php artisan wayfinder:generate --with-form

echo "✅ Wayfinder types generated"
echo "🎯 Starting PHP-FPM..."

# Start PHP-FPM
exec php-fpm
