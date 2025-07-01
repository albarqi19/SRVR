#!/bin/bash

# Alternative composer install that avoids artisan during build
set -e

echo "🔍 Installing dependencies without artisan..."
composer install --no-dev --optimize-autoloader --ignore-platform-reqs --no-interaction --no-scripts

echo "🔍 Running autoload dump without scripts..."
composer dump-autoload --optimize --ignore-platform-reqs --no-scripts

echo "✅ Composer setup completed successfully!"
