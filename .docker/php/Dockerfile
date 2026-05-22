FROM php:8.2-fpm-bookworm
ARG TIMEZONE

COPY .docker/php/php.ini /usr/local/etc/php/conf.d/docker-php-config.ini

RUN apt-get update && apt-get install -y \
    gnupg \
    g++ \
    procps \
    openssl \
    git \
    unzip \
    zlib1g-dev \
    libzip-dev \
    libfreetype6-dev \
    libpng-dev \
    libjpeg-dev \
    libicu-dev  \
    libonig-dev \
    libxslt1-dev \
    acl \
    nginx \
    supervisor \
    gettext-base \
    && echo 'alias sf="php bin/console"' >> ~/.bashrc \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-configure gd --with-jpeg --with-freetype 

RUN docker-php-ext-install \
    pdo pdo_mysql zip xsl gd intl opcache exif mbstring

# Set timezone
RUN ln -snf /usr/share/zoneinfo/${TIMEZONE} /etc/localtime && echo ${TIMEZONE} > /etc/timezone \
    && printf '[PHP]\ndate.timezone = "%s"\n', ${TIMEZONE} > /usr/local/etc/php/conf.d/tzone.ini \
    && "date"

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www/symfony

COPY composer.json composer.lock ./
COPY . .

RUN composer install --no-interaction --no-scripts

# Fix permissions for var directory
RUN mkdir -p var/cache var/log \
    && chown -R www-data:www-data var \
    && chmod -R 775 var

# Create supervisor log directory
RUN mkdir -p /var/log/supervisor

# Copy Nginx configuration
COPY .docker/nginx/templates/default.conf.template /etc/nginx/sites-available/default
RUN mkdir -p /etc/nginx/sites-enabled \
    && ln -sf /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default \
    && rm -f /etc/nginx/sites-enabled/default.dpkg-dist

# Copy supervisor configuration
COPY .docker/php/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Copy entrypoint script
COPY .docker/php/docker-entrypoint.sh /usr/local/bin/docker-entrypoint-migrations.sh
RUN chmod +x /usr/local/bin/docker-entrypoint-migrations.sh

# Expose port 80 for Nginx
EXPOSE 80

# Use custom entrypoint
ENTRYPOINT ["/usr/local/bin/docker-entrypoint-migrations.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]