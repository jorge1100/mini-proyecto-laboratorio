# PHP 8.2 + Apache para laboratorio-pedidos (PHP Vanilla, sin Composer)
FROM php:8.2-apache

# Extensiones útiles para el TP (PDO MySQL por si activan la conexión real + intl/zip comunes)
RUN docker-php-ext-install pdo pdo_mysql \
    && a2enmod rewrite

# DocumentRoot -> /public (el punto de entrada es public/index.php)
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

WORKDIR /var/www/html

# Copiar todo el proyecto (una sola carpeta) al contenedor
COPY . /var/www/html/

# Permisos para Apache
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

EXPOSE 80
