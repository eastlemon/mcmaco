#!/bin/sh
set -e

# Rebuild package cache (in case volume mount overwrote it)
php artisan package:discover --ansi || true

# Start php-fpm in background
php-fpm -D

# Run migrations (non-fatal)
php artisan migrate --force || true

# Start nginx in foreground
exec nginx -g 'daemon off;'
