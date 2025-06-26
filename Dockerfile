# Use the official PHP image as the base
FROM php:8.2-fpm

# Set working directory
WORKDIR /var/www/html

# Install system dependencies and Node.js
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libzip-dev \
    unzip \
    libicu-dev \
    gnupg \
    ca-certificates \
    # Add ImageMagick dependencies
    imagemagick \
    libmagickwand-dev \
    && curl -fsSL https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get install -y nodejs \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd mbstring zip pdo_mysql intl \
    # Install and enable the imagick extension
    && pecl install imagick \
    && docker-php-ext-enable imagick \
    # Fix ImageMagick security policy to allow PDF operations
    && sed -i 's/<policy domain="coder" rights="none" pattern="PDF" \/>/<policy domain="coder" rights="read|write" pattern="PDF" \/>/' /etc/ImageMagick-6/policy.xml \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy custom PHP configuration
COPY php.ini /usr/local/etc/php/conf.d/uploads.ini

# Fix git dubious ownership issue
RUN git config --global --add safe.directory /var/www/html

# Copy composer files first for better caching
COPY composer.json composer.lock ./

# Copy entrypoint script
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# Copy the rest of the application
COPY . .

# Install PHP dependencies
RUN composer install

# Install Node.js dependencies and build assets
RUN npm install && npm run build

# Ensure proper permissions
RUN chown -R www-data:www-data /var/www/html

# Set entrypoint
ENTRYPOINT ["docker-entrypoint.sh"]

# Start PHP-FPM
CMD ["php-fpm"]
