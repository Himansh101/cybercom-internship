#!/bin/bash
set -e

cd /var/www/html

if [ ! -d "vendor" ]; then
    echo ">>> Installing Composer dependencies..."
    composer install --no-dev --no-scripts --prefer-dist --no-interaction --ignore-platform-reqs --no-security-blocking --optimize-autoloader
    echo ">>> Done."
fi

exec apache2-foreground
