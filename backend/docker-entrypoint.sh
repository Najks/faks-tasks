#!/bin/sh
# Minimal entrypoint: do not run migrations or seeds automatically.
# The user will run seeds/migrations manually as needed.

set -e

# Start PHP-FPM (default command)
exec php-fpm
