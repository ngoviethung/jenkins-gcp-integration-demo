FROM php:7.4-alpine as composer_base

WORKDIR /var/www/html

# Install dependencies
RUN apk add --virtual build-dependencies --no-cache ${PHPIZE_DEPS} openssl
RUN apk add --no-cache libpng-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    libzip-dev

# Install extensions
#use exif_imagetype convert png to jpg
RUN docker-php-ext-install exif
RUN docker-php-ext-install pdo_mysql zip pdo
# exif for image type
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install gd

# Install extension MongoDB
RUN pecl install mongodb \
    && docker-php-ext-enable mongodb

#socket for rabitMQ
RUN docker-php-ext-install sockets

# Install PHP Composer
RUN curl -sS https://getcomposer.org/installer | php -- --version=2.5.7 --install-dir=/usr/local/bin --filename=composer
RUN addgroup -S composer \
    && adduser -S composer -G composer \
    && chown -R composer /var/www/html

USER composer

#Install all the dependencies without running any installation scripts.

COPY --chown=composer composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

#We can run the install again, and let it run any install scripts.
COPY --chown=composer . .

RUN if [ -d ".docker" ]; then rm -rf .docker; fi && \
    if [ -d ".docker-production" ]; then rm -rf .docker-production; fi && \
    if [ -d ".git" ]; then rm -rf .git; fi && \
    if [ -d "vendor" ]; then rm -rf vendor; fi && \
    if [ -d "node_modules" ]; then rm -rf node_modules; fi && \
    if [ -f ".env" ]; then rm -rf .env; fi && \
    if [ -f "pull.sh" ]; then rm -rf pull.sh; fi && \
    if [ -f "push.sh" ]; then rm -rf push.sh; fi && \
    if [ -d ".k8s" ]; then rm -rf .k8; fi
RUN composer install --no-dev --prefer-dist


#======= PHP CLI ========#

FROM php:7.4-cli as cli

# Set working directory
WORKDIR /var/www/html

# Install dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libzip-dev \
    zip unzip \
    libssl-dev #this for mongodb

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*
# Install extensions
#dung de su dung exif_imagetype convert png to jpg
RUN docker-php-ext-install exif
RUN docker-php-ext-install pdo_mysql zip pdo
# exif for image type
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install gd

# Cài đặt extension MongoDB
RUN pecl install mongodb \
    && docker-php-ext-enable mongodb

#socket for rabitMQ
RUN docker-php-ext-install sockets

COPY --from=composer_base /var/www/html /var/www/html


#======== PHP FPM ========#

FROM php:7.4-fpm as fpm_server

# Set working directory
WORKDIR /var/www/html

# Install dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libzip-dev \
    zip unzip \
    cron vim \
    htop \
    iputils-ping \
    libssl-dev #this for mongodb

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*
# Install extensions
#dung de su dung exif_imagetype convert png to jpg
RUN docker-php-ext-install exif
RUN docker-php-ext-install pdo_mysql zip pdo
# exif for image type
RUN docker-php-ext-configure gd --with-freetype --with-jpeg
RUN docker-php-ext-install gd

# Cài đặt extension MongoDB
RUN pecl install mongodb \
    && docker-php-ext-enable mongodb

#socket for rabitMQ
RUN docker-php-ext-install sockets


# As FPM uses the www-data user when running our application,
USER  www-data

COPY --from=composer_base --chown=www-data /var/www/html /var/www/html



#======== NGINX WEB SERVER ========#

FROM nginx:1.17-alpine as web_server

WORKDIR /var/www/html
COPY .docker-production/nginx.conf /etc/nginx/conf.d/default.conf

# Copy in ONLY the public directory of our project.
# This is where all the static assets will live, which nginx will serve for us.
COPY --from=composer_base /var/www/html/public /var/www/html/public



#======== CRON ========#
FROM cli as cron

WORKDIR /var/www/html

# Install dependencies
RUN apt-get update && apt-get install -y cron

# We want to create a laravel.cron file with Laravel cron settings, which we can import into crontab,
# and run crond as the primary command in the forground
RUN touch laravel.cron && \
    echo "* * * * * cd /var/www/html && php artisan schedule:run" >> laravel.cron && \
    crontab laravel.cron

CMD ["crontab", "-l"]


#======== END ========#
FROM cli