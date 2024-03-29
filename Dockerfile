FROM php:8.0-cli

# Prepare Runtime (libonig-dev provides mbstring!!)
RUN apt-get update && apt-get install -y \
        libfreetype6-dev \
        libonig-dev \
        libzip-dev \
        zip \
    && docker-php-ext-install zip \
    && docker-php-source delete

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --1 --install-dir=/usr/local/bin --filename=composer

# Copy basic app
COPY engine /usr/src/simpla/engine

# Copy default page
COPY page /usr/src/simpla/page

# Copy basic app
RUN mkdir /usr/src/simpla/dist

WORKDIR /usr/src/simpla/engine

# Install dependencies
RUN composer install --no-dev --optimize-autoloader

CMD [ "composer", "build" ]
