# World Cup 2026 Companion — zero-config container.
# Runs out of the box on the free public openfootball dataset (no keys, no DB).
#
#   docker build -t worldcup2026 .
#   docker run -p 8080:80 worldcup2026     →  http://localhost:8080
#
FROM php:8.2-apache

# System libraries needed to build the optional GD extension
# (used only for generated share-cards / OG images).
RUN apt-get update \
 && apt-get install -y --no-install-recommends \
        libpng-dev libjpeg-dev libfreetype6-dev \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install -j"$(nproc)" gd pdo_mysql \
 && apt-get clean && rm -rf /var/lib/apt/lists/*

# Honour the bundled .htaccess (URL rewriting + security deny rules)
RUN a2enmod rewrite \
 && sed -ri 's!AllowOverride None!AllowOverride All!g' /etc/apache2/apache2.conf

# Copy the application
COPY . /var/www/html/

# Runtime folders must be writable (disk cache + file-based data)
RUN chown -R www-data:www-data /var/www/html/cache /var/www/html/data

EXPOSE 80
