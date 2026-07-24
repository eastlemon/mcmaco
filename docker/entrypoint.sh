#!/bin/sh
set -e

# Start php-fpm in background
php-fpm -D

# Run migrations (non-fatal)
php artisan migrate --force || true

# Start nginx in foreground
exec nginx -g 'daemon off;'
