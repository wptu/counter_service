FROM php:8.2-apache

# Enable Apache mod_rewrite for clean URLs
RUN a2enmod rewrite

# Install PDO MySQL extension
RUN docker-php-ext-install pdo pdo_mysql

# Set timezone to Bangkok
RUN ln -snf /usr/share/zoneinfo/Asia/Bangkok /etc/localtime && echo "Asia/Bangkok" > /etc/timezone

# Configure PHP timezone
RUN echo "date.timezone = Asia/Bangkok" > /usr/local/etc/php/conf.d/timezone.ini

# Enable sessions
RUN echo "session.save_path = /tmp" >> /usr/local/etc/php/conf.d/session.ini

# Copy Apache configuration
COPY apache-config.conf /etc/apache2/sites-available/000-default.conf

# Copy source code
COPY src/ /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80

CMD ["apache2-foreground"]
