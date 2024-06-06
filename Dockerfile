# Sử dụng hình ảnh cơ bản PHP 7.4 CLI
FROM php:7.4-fpm

# Thiết lập thư mục làm việc
WORKDIR /var/www/html

# Sao chép tất cả các tệp từ thư mục hiện tại vào thư mục làm việc
COPY app app

CMD [ "php-fpm" ]