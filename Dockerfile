FROM php:8.4-apache

# Gerekli kütüphaneleri tek RUN'da yükle (layer sayısını azaltır)
RUN apt-get update && apt-get install -y \
    libpq-dev zip unzip git \
    && docker-php-ext-install pdo pdo_pgsql \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# OPcache ekle (PHP performansını ciddi artırır)
RUN docker-php-ext-install opcache
COPY opcache.ini /usr/local/etc/php/conf.d/opcache.ini

# Composer'ı kur
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Apache ayarları
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf
RUN a2enmod rewrite

# Önce sadece composer dosyalarını kopyala (cache için)
COPY composer.json composer.lock /var/www/html/
WORKDIR /var/www/html
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Sonra tüm dosyaları kopyala
COPY . /var/www/html

# İzinleri ayarla
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Bağımlılıkları kur
RUN composer install --no-dev --optimize-autoloader

# KİLİT NOKTA: Uygulama başlarken önce migrate yap, seed et, cache'le, storage link kur, sonra apache'yi çalıştır
CMD php artisan config:clear && php artisan cache:clear && php artisan migrate --force && php artisan storage:link && php artisan config:cache && php artisan route:cache && php artisan view:cache && apache2-foreground
