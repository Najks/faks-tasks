#!/bin/sh
set -e

DB_WAIT_RETRIES=${DB_WAIT_RETRIES:-60}
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

# Run migrations once (idempotent)
if php artisan migrate --force --no-interaction; then
  echo "Migrations ran successfully (or were already up-to-date)"
else
  echo "Warning: migrations failed (continuing)"
fi

# PHP inline script: bootstrap Laravel and check users table count.
# Exit codes: 0 => users_count > 0, 1 => users_count == 0, 2 => error (table missing / DB error)
php - <<'PHP'
<?php
require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
try {
    $count = Illuminate\Support\Facades\DB::table('users')->count();
    exit($count > 0 ? 0 : 1);
} catch (Throwable $e) {
    // Could be table missing or DB not ready
    // echo "DB check error: " . $e->getMessage() . PHP_EOL;
    exit(2);
}
PHP

RC=$?
if [ "$RC" -eq 0 ]; then
  echo "Seed data detected in users table, skipping db:seed."
elif [ "$RC" -eq 1 ]; then
  echo "No seed data detected; running php artisan db:seed --force"
  if php artisan db:seed --force --no-interaction; then
    echo "Seeding completed successfully."
  else
    echo "Warning: seeding failed."
  fi
else
  echo "Could not determine DB state (users table missing or DB error). Skipping seeding to avoid repeated failures."
fi

# Run the main process (php-fpm)
exec php-fpm

