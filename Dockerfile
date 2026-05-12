FROM php:8.2-apache
RUN docker-php-ext-install pdo pdo_mysql
COPY . /var/www/html/
EXPOSE 8080
ENV APACHE_RUN_USER www-data
ENV APACHE_RUN_GROUP www-data
CMD ["apache2-foreground"]
