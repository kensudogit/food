FROM php:8.1-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    libmemcached-dev \
    zlib1g-dev \
    build-essential \
    autoconf \
    libtool \
    pkg-config \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip sockets

# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Install Memcached extension
RUN pecl install memcached && docker-php-ext-enable memcached

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install gRPC and Protobuf extensions (commented out for faster build)
# RUN pecl install grpc-1.54.0 && docker-php-ext-enable grpc
# RUN pecl install protobuf-3.24.0 && docker-php-ext-enable protobuf

# Set working directory
WORKDIR /var/www/html

# Copy composer files
COPY composer.json ./
COPY composer.lock* ./

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Copy application code
COPY . .

# Set permissions
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 755 /var/www/html

# Create logs directory
RUN mkdir -p /var/www/html/logs && chown -R www-data:www-data /var/www/html/logs

# Expose port
EXPOSE 9000

# Start PHP-FPM
CMD ["php-fpm"]
