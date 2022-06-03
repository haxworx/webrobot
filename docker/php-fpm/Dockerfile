FROM php:8.1-fpm

COPY setup.sh .

RUN apt update \
    && apt install -y zlib1g-dev g++ git libicu-dev zip libzip-dev sudo libpq-dev \
    && docker-php-ext-install intl opcache pdo pdo_mysql pdo_pgsql \
    && pecl install apcu \
    && docker-php-ext-enable apcu \
    && docker-php-ext-configure zip \
    && docker-php-ext-install zip

WORKDIR /code

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN curl -sS https://get.symfony.com/cli/installer | bash
RUN mv /root/.symfony/bin/symfony /usr/local/bin/symfony
RUN git config --global user.email "noreply@alastairpoole.com" \ 
    && git config --global user.name "Alastair Poole"

RUN useradd -m -d /home/alastair alastair
RUN usermod -a -G alastair www-data

ENV TZ=Europe/London
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone


