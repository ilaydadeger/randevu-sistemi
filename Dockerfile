FROM php:8.4-apache

# Gerekli kütüphaneleri yükle
RUN apt-get update && apt-get install -y libpq-dev zip unzip git
RUN docker-php-ext-install pdo pdo_pgsql opcache

# OPcache ayarları
RUN { \
    echo "opcache.enable=1"; \
    echo "opcache.memory_consumption=128"; \
    echo "opcache.interned_strings_buffer=8"; \
    echo "opcache.max_accelerated_files=10000"; \
    echo "opcache.validate_timestamps=0"; \
    echo "opcache.save_comments=1"; \
    echo "opcache.fast_shutdown=1"; \
} >> /usr/local/etc/php/conf.d/opcache.ini

# Composer'ı kur
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Apache ayarları
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf
RUN a2enmod rewrite

# Apache keepalive
RUN echo "KeepAlive On\nKeepAliveTimeout 5\nMaxKeepAliveRequests 100" \
    >> /etc/apache2/apache2.conf

# Dosyaları kopyala
COPY . /var/www/html
WORKDIR /var/www/html

# İzinleri ayarla
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Node.js kur
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - && \
    apt-get install -y nodejs

# Bağımlılıkları kur
RUN composer install --no-dev --optimize-autoloader --classmap-authoritative

# NPM bağımlılıklarını kur ve Vite build al
RUN npm install && npm run build
# Container başladığında (build değil, runtime'da) çalışır:
# Bu sayede Render ortamında DB bağlantısı ve SSL hazır olduğunda migrate/optimize edilir.
CMD php artisan optimize:clear --no-interaction && \
    php artisan migrate --force --no-interaction && \
    php artisan db:seed --force --no-interaction && \
    php artisan storage:link --no-interaction && \
    php artisan optimize --no-interaction && \
    php artisan event:cache --no-interaction && \
    apache2-foreground
