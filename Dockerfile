FROM php:8.5-cli

# Prepare Runtime (libonig-dev provides mbstring!!)
RUN apt-get update && apt-get install -y \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libwebp-dev \
        libonig-dev \
        libzip-dev \
        zip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) gd zip \
    && docker-php-source delete \
    && rm -rf /var/lib/apt/lists/*

# Install Composer (v2 by default)
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

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
