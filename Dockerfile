FROM php:8.2-apache

RUN a2dismod mpm_event mpm_worker mpm_prefork || true \
    && a2enmod mpm_prefork

RUN docker-php-ext-install mysqli pdo pdo_mysql

COPY project_tip/ /var/www/html/

RUN chown -R www-data:www-data /var/www/html

EXPOSE 80