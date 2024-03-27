# Start from base
FROM php:8.2-apache

# Copy all the files to the container
COPY . /var/www/html/

# Fetch app specific deps
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli
RUN docker-php-ext-install session && docker-php-ext-enable session