FROM php:8.2-apache

# Install mysqli extension for database connection
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

# Copy source code
COPY ./src /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html
