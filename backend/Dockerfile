FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    libpq-dev git unzip libzip-dev \
    && docker-php-ext-install pdo pdo_pgsql zip

RUN a2enmod rewrite

# FIX: Use the actual path directly, not an env variable in sed single-quotes
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/*.conf \
 && sed -ri -e 's!/var/www/html!/var/www/html/public!g' /etc/apache2/apache2.conf

COPY . /var/www/html/

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
 && cd /var/www/html && composer install --no-dev --optimize-autoloader

COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 10000
ENTRYPOINT ["/bin/bash", "/usr/local/bin/docker-entrypoint.sh"]