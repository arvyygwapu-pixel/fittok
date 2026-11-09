# Use the official PHP image with Apache web server
FROM php:8.2-apache

# Install the PDO MySQL extension required for your Aiven database connection
RUN docker-php-ext-install pdo pdo_mysql

# Enable Apache mod_rewrite (recommended for API routing)
RUN a2enmod rewrite

# Copy all your project files (including the api/ folder and ca.pem) to the server root
COPY . /var/www/html/

# Set the correct permissions for the Apache web server user
RUN chown -R www-data:www-data /var/www/html/

# Render routes external traffic to the port exposed by the container
EXPOSE 80