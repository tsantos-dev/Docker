# Use uma imagem base oficial do PHP com FPM
FROM php:8.2-fpm

# Argumentos para UID/GID para evitar problemas de permissão com volumes montados
ARG UID
ARG GID

# Variáveis de ambiente para Xdebug (podem ser sobrescritas no docker-compose.yml ou .env)
ENV PHP_IDE_CONFIG="serverName=docker-php-apache"
ENV XDEBUG_MODE="off" 
ENV XDEBUG_CONFIG="client_host=host.docker.internal client_port=9003 log_level=0"

# Instala dependências do sistema necessárias para as extensões PHP
RUN apt-get update && apt-get upgrade -y && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    libicu-dev \
    zip \
    unzip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Instala extensões PHP
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-install pdo pdo_mysql mysqli mbstring exif pcntl bcmath zip intl opcache

# Instala Xdebug (opcional, mas útil para desenvolvimento)
RUN pecl install xdebug && docker-php-ext-enable xdebug
COPY xdebug.ini /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# Instala Composer globalmente
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Define o diretório de trabalho
WORKDIR /var/www/html/app

# Altera o usuário padrão do PHP-FPM para corresponder ao usuário do host (opcional, mas bom para desenvolvimento)
RUN if [ "${UID}" != "" ] && [ "${GID}" != "" ]; then \
    usermod -u ${UID} www-data && \
    groupmod -g ${GID} www-data; \
    fi

# Expõe a porta do PHP-FPM (não é necessário mapear no docker-compose se for apenas para comunicação interna)
# EXPOSE 9000

# Comando padrão (já definido na imagem base, mas pode ser explicitado)
CMD ["php-fpm"]