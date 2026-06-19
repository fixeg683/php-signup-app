FROM php:8.2-cli

# Install PostgreSQL and zip extensions
RUN apt-get update && apt-get install -y \
    libpq-dev unzip git libzip-dev \
    && docker-php-ext-install pdo pdo_pgsql zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- \
    --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www/html

# Copy and install dependencies
COPY . .
RUN composer install --no-dev --optimize-autoloader

EXPOSE 10000

# PHP built-in server — reads $PORT from Render's environment at runtime
CMD php -S 0.0.0.0:${PORT:-10000} -t /var/www/html/public