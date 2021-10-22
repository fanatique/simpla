FROM php:8.1-rc-cli

# Prepare Runtime (libonig-dev provides mbstring!!)
RUN apt-get update && apt-get install -y \
        libfreetype6-dev \
        libonig-dev \
        libzip-dev \
        zip \
    && docker-php-ext-install zip \
    && docker-php-source delete

# Install Composer
# Copy default page
COPY install-composer.sh /install-composer.sh

RUN /bin/sh /install-composer.sh

# Copy basic app
COPY engine/ /usr/src/simpla

# Copy default page
COPY page /usr/src/simpla/page

WORKDIR /usr/src/simpla

# Install dependencies
RUN composer install --no-dev --optimize-autoloader

CMD [ "composer", "build" ]
