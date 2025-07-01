#!/bin/bash

# Debug script for composer install issues
set -e

echo "🔍 Checking Composer setup..."
composer --version

echo "🔍 Checking PHP version and extensions..."
php --version
php -m | grep -E "(intl|gd|sodium|zip|exif)" || echo "Some extensions missing"

echo "🔍 Validating composer.json..."
composer validate

echo "🔍 Running composer diagnose..."
composer diagnose

echo "🔍 Showing platform info..."
composer show --platform

echo "🔍 Installing dependencies..."
composer install --no-dev --optimize-autoloader --ignore-platform-reqs --no-interaction --no-scripts --verbose

echo "🔍 Running optimized autoload dump..."
composer dump-autoload --optimize --ignore-platform-reqs

echo "✅ Composer install completed successfully!"
