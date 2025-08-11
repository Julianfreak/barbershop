FROM php:8.2-apache

# Instalar la extensión mysqli
RUN docker-php-ext-install mysqli

# Copia personalizada (opcional, si quieres añadir config extra)
# COPY ./config/php.ini /usr/local/etc/php/
