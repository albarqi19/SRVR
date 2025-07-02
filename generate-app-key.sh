#!/bin/bash

echo "🔑 Generating Laravel APP_KEY for Railway deployment..."

# Check if artisan exists
if [ ! -f "artisan" ]; then
    echo "❌ artisan file not found. Please run this from Laravel project root."
    exit 1
fi

# Generate key
echo "🔍 Generating application key..."
KEY=$(php artisan key:generate --show)

echo ""
echo "✅ Generated APP_KEY:"
echo "APP_KEY=$KEY"
echo ""
echo "📋 Copy this key and add it to Railway environment variables:"
echo "   Variable Name: APP_KEY"
echo "   Variable Value: $KEY"
echo ""
echo "🚀 After adding the key, your Laravel app will be ready on Railway!"
