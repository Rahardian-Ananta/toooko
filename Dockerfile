# Dockerfile
FROM php:8.2-apache

# Optional: set zona waktu
ENV TZ=Asia/Jakarta

# Copy source ke DocumentRoot Apache
COPY htdocs/ /var/www/html/

# Aktifkan mod_rewrite (kalau nanti perlu)
RUN a2enmod rewrite

# Pastikan folder storage bisa ditulis PHP
RUN chown -R www-data:www-data /var/www/html/storage

# (Opsional) PHP extensions ringan bisa ditambah di sini
# RUN docker-php-ext-install pdo pdo_mysql

# Apache listen di 80 (Render akan map otomatis)
EXPOSE 80
