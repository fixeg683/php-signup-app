#!/bin/bash
set -e

# Render sets $PORT at runtime — default to 10000 if not set
PORT="${PORT:-10000}"

echo "Starting Apache on port $PORT"

# Update Apache to listen on Render's assigned port
sed -i "s/Listen 80/Listen $PORT/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost *:$PORT>/" /etc/apache2/sites-available/000-default.conf

# Set Apache's environment port for the virtual host
export APACHE_LISTEN=$PORT

apache2-foreground