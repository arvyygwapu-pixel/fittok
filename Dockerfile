FROM php:8.2-apache

# Use production PHP settings (hides HTML warnings from breaking JSON responses)
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Install MySQL PDO extension
RUN docker-php-ext-install pdo pdo_mysql

# Enable Apache rewrite and silence ServerName log noise
RUN a2enmod rewrite && echo "ServerName localhost" >> /etc/apache2/apache2.conf

# Copy project files
COPY . /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html/

# Render port
EXPOSE 10000

CMD ["bash", "-c", "PORT=${PORT:-10000}; sed -i \"s/^Listen 80$/Listen ${PORT}/\" /etc/apache2/ports.conf; sed -i \"s/<VirtualHost \\*:80>/<VirtualHost *:${PORT}>/\" /etc/apache2/sites-available/000-default.conf; exec apache2-foreground"]