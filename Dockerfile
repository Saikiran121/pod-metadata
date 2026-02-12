FROM php:8.2-apache
WORKDIR /var/www/html
COPY index.php style.css /var/www/html/

RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html

RUN a2enmod rewrite

EXPOSE 80

LABEL MAINTAINER="ArgoCD-Lab"
LABEL DESCRIPTION="Kubernetes Pod Metadata Diagnostic App"
