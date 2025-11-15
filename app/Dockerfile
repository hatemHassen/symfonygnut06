FROM php:8.2-apache

WORKDIR /var/www/app
ENV APP_ENV=prod
ENV APP_DEBUG=0

# -------------------------------------------------------
# System dependencies (one apt-get call only)
# -------------------------------------------------------
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf && \
    apt-get update && apt-get install -y --no-install-recommends \
    git curl wget nano locales ca-certificates unzip \
    libicu-dev libxslt1.1 libpng-dev libzip-dev libxml2-dev libonig-dev \
    openssl gnupg && \
    rm -rf /var/lib/apt/lists/*

# -------------------------------------------------------
# Locales
# -------------------------------------------------------
RUN echo "en_US.UTF-8 UTF-8" > /etc/locale.gen && \
    echo "fr_FR.UTF-8 UTF-8" >> /etc/locale.gen && \
    locale-gen

# -------------------------------------------------------
# PHP Extensions (FAST install — no compilation)
# -------------------------------------------------------
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions

RUN install-php-extensions \
    intl gd xsl zip mbstring dom pdo_mysql mysqli calendar opcache apcu xdebug

# Enable Apache modules
RUN a2enmod rewrite ssl

# -------------------------------------------------------
# Symfony CLI
# -------------------------------------------------------
RUN wget https://get.symfony.com/cli/installer -O - | bash && \
    mv /root/.symfony*/bin/symfony /usr/local/bin/symfony

# -------------------------------------------------------
# Node.js (fast installation)
# -------------------------------------------------------
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - && \
    apt-get update && apt-get install -y nodejs && \
    npm install -g npm

# -------------------------------------------------------
# SSL self-signed certificate
# -------------------------------------------------------
RUN mkdir -p /etc/apache2/ssl && \
    openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
    -keyout /etc/apache2/ssl/apache.key \
    -out /etc/apache2/ssl/apache.crt \
    -subj "/C=FR/ST=France/L=Paris/O=Dev/OU=Dev/CN=localhost"

COPY ./config/default-ssl.conf /etc/apache2/sites-available/default-ssl.conf
RUN a2ensite default-ssl

# -------------------------------------------------------
# Xdebug configuration
# -------------------------------------------------------
COPY ./config/xdebug.ini /etc/php/8.2/apache2/conf.d/30-xdebug.ini
COPY ./config/xdebug.ini /etc/php/8.2/cli/conf.d/30-xdebug.ini

# -------------------------------------------------------
# Composer (fastest method: use the official binary)
# -------------------------------------------------------
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# -------------------------------------------------------
# Copy project files with PERMISSIONS applied instantly
# (much faster than chown -R !)
# -------------------------------------------------------
COPY --chown=www-data:www-data . /var/www/app

# Install PHP dependencies without scripts for speed
# Increase memory + disable xdebug to avoid crashes
# Install dependencies without running Symfony scripts
RUN COMPOSER_MEMORY_LIMIT=-1 \
    composer install \
        --no-interaction \
        --prefer-dist \
        --optimize-autoloader \
        --no-scripts



EXPOSE 80 443
CMD ["apache2-foreground"]
