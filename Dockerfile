# Use official PHP with Apache

FROM php:8.2-apache



# Install required PHP extensions for Laravel

RUN apt-get update && apt-get install -y \
    git unzip libpq-dev libzip-dev zip \
    libpng-dev libjpeg-dev libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql zip gd



# Enable Apache mod_rewrite (needed for Laravel routes)

RUN a2enmod rewrite



# Set Apache DocumentRoot to /var/www/html/public (Laravel entry point)

RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf \
    && sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/apache2.conf



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



# Set permissions for Laravel storage and cache

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Create startup script with migrations
RUN echo '#!/bin/bash\n\
    echo "Running migrations..."\n\
    php artisan migrate --force\n\
    echo "Seeding admin..."\n\
    php artisan db:seed --class=AdminSeeder --force\n\
    echo "Clearing cache..."\n\
    php artisan config:cache\n\
    php artisan route:cache\n\
    php artisan view:cache\n\
    echo "Starting queue worker..."\n\
    php artisan queue:work --daemon &\n\
    echo "Starting Apache..."\n\
    apache2-foreground' > /usr/local/bin/start.sh \
    && chmod +x /usr/local/bin/start.sh

# Expose Render's required port
EXPOSE 10000

# Start Apache with migrations and queue worker
CMD ["/usr/local/bin/start.sh"]










