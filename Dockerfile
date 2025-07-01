FROM php:8.2-apache

# Install system dependencies in stages to reduce network issues
RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extension dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libicu-dev \
    libzip-dev \
    libsodium-dev \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-configure intl \
    && docker-php-ext-install \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        intl \
        sodium \
        zip

# Install Node.js and npm
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get install -y nodejs

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Enable Apache modules
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www/html

# Copy composer files first for better caching
COPY composer.json composer.lock ./

# Install PHP dependencies with platform requirements ignored
RUN composer install --no-dev --optimize-autoloader --ignore-platform-reqs --no-scripts

# Copy application files
COPY . .

# Copy package.json if exists and install frontend dependencies
RUN if [ -f package.json ]; then npm install && npm run build; fi

# Create required directories and set permissions
RUN mkdir -p \
        storage/app/public \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/testing \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache \
    && chown -R www-data:www-data /var/www/html

# Configure Apache document root
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Set environment variables
ENV APP_ENV=production
ENV APP_DEBUG=false

# Expose port
EXPOSE 80

# Health check
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD curl -f http://localhost/ || exit 1

# Start Apache
CMD ["apache2-foreground"]
