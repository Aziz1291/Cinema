FROM php:8.2-apache

# Install PDO MySQL extension
RUN docker-php-ext-install pdo pdo_mysql
# Never show PHP errors to visitors; send them to the container log instead.
RUN printf 'display_errors=Off\ndisplay_startup_errors=Off\nlog_errors=On\nerror_log=/dev/stderr\nexpose_php=Off\n' > /usr/local/etc/php/conf.d/zz-production.ini

# Enable Apache rewrite module
RUN a2enmod rewrite

# Copy project files
COPY . /var/www/html/

# Set correct permissions
RUN chown -R www-data:www-data /var/www/html/

# Expose port 80
EXPOSE 80
