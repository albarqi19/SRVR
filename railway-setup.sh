#!/bin/bash

# Railway deployment script
set -e

echo "🚀 Starting Railway deployment..."

# Create required directories
mkdir -p storage/framework/cache/data
mkdir -p storage/framework/sessions  
mkdir -p storage/framework/views
mkdir -p storage/logs
mkdir -p bootstrap/cache

# Set permissions
chmod -R 755 storage
chmod -R 755 bootstrap/cache

echo "✅ Railway deployment setup complete!"
