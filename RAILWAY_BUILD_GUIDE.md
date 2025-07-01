# Railway Build Guide for GARB Project

## Overview
This guide explains how the GARB Laravel project is configured for deployment on Railway with proper PHP extensions and dependencies.

## Build Configuration

### 1. Docker Approach (Recommended)
The project includes a comprehensive `Dockerfile` that:
- Uses PHP 8.2 with Apache
- Installs all required PHP extensions (intl, gd, exif, sodium, zip)
- Handles Composer dependencies with `--ignore-platform-reqs`
- Builds frontend assets if package.json exists
- Sets proper permissions and configurations

### 2. Nixpacks Approach (Alternative)
The `nixpacks.toml` configuration:
- Uses PHP 8.2 with all required extensions
- Runs `railway-setup.sh` for environment preparation
- Installs dependencies with platform requirements ignored
- Handles both PHP and Node.js builds

## Required PHP Extensions
- **intl**: For internationalization support
- **gd**: For image processing
- **exif**: For image metadata
- **sodium**: For cryptographic functions
- **zip**: For compression support
- **pdo_mysql**: For database connectivity
- **mbstring**: For multibyte string handling
- **bcmath**: For arbitrary precision mathematics

## Environment Variables on Railway
Set these environment variables in Railway dashboard:

### Database (Auto-configured by Railway MySQL)
- `MYSQLHOST`
- `MYSQLPORT`
- `MYSQLDATABASE`
- `MYSQLUSER`
- `MYSQLPASSWORD`

### Application
- `APP_KEY`: Generate with `php artisan key:generate --show`
- `APP_ENV`: `production`
- `APP_DEBUG`: `false`
- `APP_URL`: Your Railway domain

## Troubleshooting Build Issues

### Issue 1: Missing PHP Extensions
**Solution**: Use the provided Dockerfile or ensure nixpacks.toml includes all extensions.

### Issue 2: Composer Install Fails
**Solution**: Use `--ignore-platform-reqs` flag in composer install commands.

### Issue 3: Permission Errors
**Solution**: The railway-setup.sh script creates directories and sets permissions.

### Issue 4: Frontend Build Fails
**Solution**: The build process checks for package.json existence before running npm commands.

### Issue 5: Database Connection Issues
**Solution**: Use Railway's MySQL environment variables as configured in .env.example.

## Build Commands

### Using Docker
Railway will automatically use the Dockerfile if present.

### Using Nixpacks
1. Install dependencies: `composer install --ignore-platform-reqs`
2. Setup environment: `bash railway-setup.sh`
3. Generate key: `php artisan key:generate`
4. Build assets: `npm run build` (if package.json exists)
5. Cache configs: `php artisan config:cache`

## Post-Deployment Setup
1. Run migrations: `php artisan migrate --force`
2. Seed database: `php artisan db:seed --force`
3. Clear caches: `php artisan cache:clear`
4. Create storage link: `php artisan storage:link`

## Key Files for Railway
- `Dockerfile`: Complete Docker configuration
- `nixpacks.toml`: Nixpacks build configuration
- `railway-setup.sh`: Environment setup script
- `.env.example`: Environment template
- `Procfile`: Process definition (if needed)
- `railway.json`: Railway-specific settings

## Tips for Successful Deployment
1. Always use `--ignore-platform-reqs` for composer in production
2. Ensure all required directories exist (storage/*, bootstrap/cache)
3. Set proper file permissions (755 for directories, 644 for files)
4. Use Railway's MySQL service for database
5. Set APP_KEY before deployment
6. Monitor Railway logs for build and runtime issues

## Support
- Check Railway logs for detailed error messages
- Verify all environment variables are set correctly
- Ensure database connection is working
- Test locally with same PHP version (8.2)
