FROM php:5.6-fpm

RUN sed -i 's/deb.debian.org/archive.debian.org/g' /etc/apt/sources.list
RUN sed -i 's/security.debian.org/archive.debian.org/g' /etc/apt/sources.list
RUN sed -i '/stretch-updates/d' /etc/apt/sources.list

ENV DEBIAN_FRONTEND=noninteractive

# debian packages
RUN apt-get update && apt-get install -y \
    git \
    curl \
    vim \
    libicu-dev \
    libpq-dev \
    unzip \
    zlib1g-dev \
    libonig-dev \
    libzip-dev

# php modules
RUN docker-php-ext-install -j$(nproc) \
    intl \
    mbstring \
    pgsql \
    pdo \
    pdo_pgsql \
    zip

# php composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
ENV COMPOSER_ALLOW_SUPERUSER=1

WORKDIR /src
