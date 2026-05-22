#!/bin/sh
set -e

echo "🚀 Starting PHP-FPM + Nginx container..."

# Process Nginx template (replace ${NGINX_BACKEND_DOMAIN} with _ for catch-all)
export NGINX_BACKEND_DOMAIN="${NGINX_BACKEND_DOMAIN:-_}"
envsubst '${NGINX_BACKEND_DOMAIN}' < /etc/nginx/sites-available/default > /etc/nginx/sites-available/default.tmp
mv /etc/nginx/sites-available/default.tmp /etc/nginx/sites-available/default

# Wait for database to be ready
echo "⏳ Waiting for database..."
until php bin/console doctrine:query:sql "SELECT 1" > /dev/null 2>&1; do
    echo "Database is unavailable - sleeping"
    sleep 2
done

echo "✅ Database is ready!"

# Clear cache
echo "🧹 Clearing cache..."
php bin/console cache:clear --no-warmup
php bin/console cache:warmup

echo "✅ Ready to handle requests!"

# Execute the original entrypoint
exec docker-php-entrypoint "$@"
