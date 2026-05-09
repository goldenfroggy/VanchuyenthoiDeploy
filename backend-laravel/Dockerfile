# Sử dụng môi trường PHP chuẩn
FROM php:8.2-cli

# Cài đặt các thư viện lõi cho Laravel và MySQL
RUN apt-get update && apt-get install -y \
    libzip-dev \
    unzip \
    && docker-php-ext-install pdo pdo_mysql zip

# Cài đặt Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Chép toàn bộ source code của bạn vào Docker
WORKDIR /app
COPY . .

# Cài đặt các thư viện của dự án
RUN composer install --optimize-autoloader --no-dev

# Khởi động server API
CMD [ "sh", "-c", "php -S 0.0.0.0:${PORT:-10000} -t public" ]