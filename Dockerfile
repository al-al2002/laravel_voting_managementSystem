# Use official PHP with Apache

FROM php:8.2-apache



# Install Node.js 20.x and required PHP extensions for Laravel

RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get update && apt-get install -y \
    nodejs git unzip libpq-dev libzip-dev zip \
    libpng-dev libjpeg-dev libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql zip gd



# Enable Apache modules (needed for Laravel routes)

RUN a2enmod rewrite dir env mime negotiation



# Configure Apache ports and virtual host
RUN echo "Listen 10000" > /etc/apache2/ports.conf \
    && echo "Listen 80" >> /etc/apache2/ports.conf



# Copy custom Apache configuration
COPY apache-laravel.conf /etc/apache2/sites-available/000-default.conf



# Configure Apache to allow .htaccess overrides
RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf



# Copy app code

COPY . /var/www/html/



# Create uploads folder and set permissions

RUN mkdir -p /var/www/html/public/uploads \
    && chown -R www-data:www-data /var/www/html/public/uploads \
    && chmod -R 775 /var/www/html/public/uploads





# Set working dir

WORKDIR /var/www/html



# Install Composer //changes

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer



# Install Laravel dependencies

RUN composer install --no-dev --optimize-autoloader



# Install NPM dependencies and build assets
RUN npm install && npm run build



# Set permissions for Laravel storage and cache

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache \
    && chown -R www-data:www-data /var/www/html/public \
    && chmod -R 755 /var/www/html/public

# Create startup script with migrations
RUN echo '#!/bin/bash\n\
    set -e\n\
    echo "=== Laravel Voting System Startup ==="\n\
    echo "Verifying public directory..."\n\
    ls -la /var/www/html/public/ || echo "Public directory not found!"\n\
    if [ -f /var/www/html/public/index.php ]; then\n\
    echo "✓ index.php exists"\n\
    else\n\
    echo "✗ ERROR: index.php not found!"\n\
    fi\n\
    echo "Running migrations..."\n\
    php artisan migrate --force || echo "Migration failed, continuing..."\n\
    echo "Seeding admin..."\n\
    php artisan db:seed --class=AdminSeeder --force || echo "Seeding failed, continuing..."\n\
    echo "Clearing caches..."\n\
    php artisan config:clear || true\n\
    php artisan route:clear || true\n\
    php artisan view:clear || true\n\
    php artisan cache:clear || true\n\
    php artisan optimize:clear || true\n\
    echo "Checking mail configuration..."\n\
    php artisan tinker --execute="echo config(\"'\"'mail.mailers.smtp.host\"'\"');" || true\n\
    echo "Caching config and views..."\n\
    php artisan config:cache || true\n\
    php artisan view:cache || true\n\
    echo "Creating storage link..."\n\
    php artisan storage:link || true\n\
    echo "Setting final permissions..."\n\
    chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache || true\n\
    chmod -R 755 /var/www/html/public || true\n\
    echo "Apache configuration:"\n\
    cat /etc/apache2/sites-available/000-default.conf\n\
    echo "Tailing Laravel logs in background..."\n\
    tail -f /var/www/html/storage/logs/laravel.log &\n\
    echo "=== Starting Apache on port 10000 ==="\n\
    apache2-foreground' > /usr/local/bin/start.sh \
    && chmod +x /usr/local/bin/start.sh

# Expose Render's required port
EXPOSE 10000

# Start Apache with migrations and queue worker
CMD ["/usr/local/bin/start.sh"]










