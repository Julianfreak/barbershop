FROM php:8.2-apache

# Instalar la extensión mysqli
RUN docker-php-ext-install mysqli

# Habilitar mod_rewrite para soporte de .htaccess
RUN a2enmod rewrite headers

# Configuración de PHP: no mostrar errores al cliente
RUN echo 'display_errors = Off\nlog_errors = On\nerror_log = /dev/stderr' \
    > /usr/local/etc/php/conf.d/production.ini

EXPOSE 80

# Healthcheck: verifica que Apache responde
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD curl -f http://localhost/ || exit 1
