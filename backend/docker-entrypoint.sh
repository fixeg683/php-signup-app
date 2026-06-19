#!/bin/bash
set -e

PORT="${PORT:-10000}"
echo "Starting Apache on port $PORT"

# Fix ports.conf
sed -i "s/Listen 80/Listen $PORT/" /etc/apache2/ports.conf

# Rewrite the entire VirtualHost config cleanly so Directory block is INSIDE it
cat > /etc/apache2/sites-available/000-default.conf << EOF
<VirtualHost *:$PORT>
    DocumentRoot /var/www/html/public
    ServerName localhost

    <Directory /var/www/html/public>
        AllowOverride All
        Require all granted
        Options -Indexes +FollowSymLinks
        DirectoryIndex index.php
    </Directory>

    ErrorLog \${APACHE_LOG_DIR}/error.log
    CustomLog \${APACHE_LOG_DIR}/access.log combined
</VirtualHost>
EOF

echo "Apache config written for port $PORT"
exec apache2-foreground