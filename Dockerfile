FROM php:7.4-apache
# Instalar extensiones necesarias (ejemplo para MySQL)
RUN docker-php-ext-install mysqli pdo pdo_mysql
# Habilitar mod_rewrite para rutas amigables (común en PHP)
RUN a2enmod rewrite