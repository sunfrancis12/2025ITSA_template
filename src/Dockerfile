FROM php:8.1-apache

# 安裝 PHP 擴展
RUN docker-php-ext-install pdo pdo_mysql mysqli

# 啟用 Apache 模組
RUN a2enmod rewrite headers

# 設定 Apache 允許 .htaccess
RUN echo '<Directory /var/www/html>\n    AllowOverride All\n    Require all granted\n</Directory>' > /etc/apache2/conf-available/allow-override.conf
RUN a2enconf allow-override

# 設定時區
ENV TZ=Asia/Taipei
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# 複製檔案
COPY . /var/www/html/

# 設定權限
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 755 /var/www/html