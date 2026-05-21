#!/bin/sh
set -e

echo "🚀 Starting PHP-FPM container..."

# Wait for database to be ready
echo "⏳ Waiting for database..."
until php bin/console doctrine:query:sql "SELECT 1" > /dev/null 2>&1; do
    echo "Database is unavailable - sleeping"
    sleep 2
done

echo "✅ Database is ready!"

# Run migrations
echo "🔄 Running database migrations..."
php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration

# Clear cache
echo "🧹 Clearing cache..."
php bin/console cache:clear --no-warmup
php bin/console cache:warmup

echo "✅ Ready to handle requests!"

# Execute the original entrypoint
exec docker-php-entrypoint "$@"
