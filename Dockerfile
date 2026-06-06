FROM php:8.4-apache

# Gerekli kütüphaneleri yükle
RUN apt-get update && apt-get install -y libpq-dev zip unzip git
RUN docker-php-ext-install pdo pdo_pgsql

# Composer'ı kur
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Apache ayarları
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/sites-available/000-default.conf
RUN a2enmod rewrite

# Dosyaları kopyala
COPY . /var/www/html
WORKDIR /var/www/html

# İzinleri ayarla
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Bağımlılıkları kur
RUN composer install --no-dev --optimize-autoloader

# KİLİT NOKTA: Uygulama başlarken önce migrate yap, sonra apache'yi çalıştır
CMD php artisan migrate --force && apache2-foreground