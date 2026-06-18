FROM php:8.2-apache

# Install system dependencies, zip tools, and PostgreSQL developer libraries
RUN apt-get update && apt-get install -y \
    libpq-dev \
    git \
    unzip \
    libzip-dev \
    && docker-php-ext-install pdo pdo_pgsql zip

# Enable Apache mod_rewrite for clean routing handling
RUN a2enmod rewrite

# Change the Apache Document Root to point to your public directory
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf

# Copy application files to the container destination
COPY . /var/www/html/

# Install Composer dependencies automatically inside the container build process
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN cd /var/www/html && composer install --no-dev --optimize-autoloader

# Expose standard port
# Expose Render's dynamic port
ENV PORT=10000
EXPOSE 10000

# Override Apache to listen on $PORT
CMD sed -i "s/Listen 80/Listen ${PORT}/" /etc/apache2/ports.conf && \
    sed -i "s/:80>/:${PORT}>/" /etc/apache2/sites-available/*.conf && \
    apache2-foreground
