#!/bin/sh
set -e

# Wait for database to be ready by trying artisan migrate:status
DB_WAIT_RETRIES=${DB_WAIT_RETRIES:-30}
COUNT=0
until php artisan migrate:status > /dev/null 2>&1; do
  COUNT=$((COUNT+1))
  if [ "$COUNT" -ge "$DB_WAIT_RETRIES" ]; then
    echo "Timed out waiting for database after $DB_WAIT_RETRIES attempts, continuing..."
    break
  fi
  echo "Waiting for database... ($COUNT/$DB_WAIT_RETRIES)"
  sleep 2
done

INIT_MARKER=/var/www/html/.initialized
if [ ! -f "$INIT_MARKER" ]; then
  echo "Running migrations and seed (first-time initialization)..."
  # Run migrations and seed, but don't fail the container if it fails — log and continue
  if php artisan migrate --force; then
    echo "Migrations ran successfully"
  else
    echo "Warning: migrations failed"
  fi

  if php artisan db:seed --force; then
    echo "Seeding ran successfully"
  else
    echo "Warning: seeding failed"
  fi

  # create marker so we don't re-run on restart
  touch "$INIT_MARKER"
else
  echo "Initialization marker found, skipping migrations/seeding"
fi

# Execute the main process (php-fpm)
exec php-fpm

