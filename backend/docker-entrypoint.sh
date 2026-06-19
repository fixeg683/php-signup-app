#!/bin/bash
set -e

PORT="${PORT:-10000}"
echo "Starting Apache on port $PORT"

# Fix ports.conf
sed -i "s/Listen 80/Listen $PORT/" /etc/apache2/ports.conf

# Fix VirtualHost port — escape the * for sed
sed -i "s/<VirtualHost \*:80>/<VirtualHost *:$PORT>/" /etc/apache2/sites-available/000-default.conf

# Allow .htaccess overrides for mod_rewrite to work
cat >> /etc/apache2/sites-available/000-default.conf << EOF

<Directory /var/www/html/public>
    AllowOverride All
    Require all granted
</Directory>
EOF

exec apache2-foreground