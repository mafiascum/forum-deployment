FROM php:8.1.31-apache

ARG PHP_INI_DEVELOPMENT
ENV PHP_INI_DEVELOPMENT=$PHP_INI_DEVELOPMENT

# Install system dependencies and PHP development libraries
RUN apt-get update && apt-get -y upgrade && apt-get install -y \
    zip \
    unzip \
    awscli \
    rsync \
    jq \
    cron \
    apache2-dev \
    libmariadb3 \
    libmariadb-dev \
    mariadb-client \
    python3-pip \
    gcc \
    g++ \
    cmake \
    logrotate \
    nano \
    libicu-dev \
    python3-mysqldb \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        gd \
        mysqli \
        intl \
        opcache \
        zip \
    && docker-php-ext-enable mysqli intl opcache

# Install APCu from PECL
RUN pecl install apcu \
    && docker-php-ext-enable apcu

# Configure opcache
RUN { \
        echo 'opcache.enable=1'; \
        echo 'opcache.memory_consumption=256'; \
        echo 'opcache.interned_strings_buffer=16'; \
        echo 'opcache.max_accelerated_files=10000'; \
        echo 'opcache.revalidate_freq=2'; \
        echo 'opcache.fast_shutdown=1'; \
    } > /usr/local/etc/php/conf.d/opcache.ini

# Configure APCu
RUN { \
        echo 'apc.enabled=1'; \
        echo 'apc.shm_size=256M'; \
        echo 'apc.enable_cli=0'; \
    } > /usr/local/etc/php/conf.d/apcu.ini

RUN mkdir -p /opt/mafiascum/scripts

# Change this to change the version
ENV PHPBB_MAJOR_VERSION=3
ENV PHPBB_MINOR_VERSION=3
ENV PHPBB_PATCH_VERSION=14

# copy legacy sites
COPY www /opt/mafiascum/www

# copy vhost templates
COPY vhost_templates /opt/mafiascum/apache/vhost-templates

# add bash alias for all users
RUN echo "alias ll='ls -lA'" >> /etc/bash.bashrc

# logrotate
COPY logrotate.d/apache2 /etc/logrotate.d/apache2

# add scripts
COPY scripts /opt/mafiascum/scripts
RUN chmod +x /opt/mafiascum/scripts/*

# # set up ftp environment
RUN useradd -u 1010 -g 0 ftp_user

# php environment
RUN /opt/mafiascum/scripts/ms_env_php.sh

# add apache utils
RUN mkdir -p /opt/mafiascum/apache

# install phpbb
RUN /opt/mafiascum/scripts/ms_unpack_phpbb.sh

# add other web targets
RUN mkdir -p /opt/mafiascum/www
RUN mkdir -p /opt/mafiascum/wiki

# install pgn4web for chess tags
RUN /opt/mafiascum/scripts/ms_install_pgn4web.sh

# install access log parser
RUN /opt/mafiascum/scripts/ms_install_access_parser.sh

# install wikimedia and extensions
COPY wiki/LocalSettings.php /tmp/
RUN /opt/mafiascum/scripts/ms_install_mediawiki.sh

# monkey patched files. this is your LAST RESORT for doing custom script work - try to use extensions if at all possible.
RUN mkdir /opt/mafiascum/scripts/patches
COPY forum/patches /opt/mafiascum/scripts/patches
RUN rsync -avz /opt/mafiascum/scripts/patches/ /opt/mafiascum/forum/

# add cron
COPY cron.d /etc/cron.d/

# make log locations for directories
RUN /opt/mafiascum/scripts/ms_logs.sh

# add docker config.php.tpl
COPY forum/config.php.tpl /opt/mafiascum/forum

# internal extensions and styles
RUN mkdir -p /opt/mafiascum/forum/ext/mafiascum
COPY extensions /opt/mafiascum/forum/ext/mafiascum
COPY styles/ /opt/mafiascum/forum/styles/

# external extension build
COPY external-extensions.json /opt/mafiascum/forum
RUN /opt/mafiascum/scripts/ms_install_extensions_external.sh

# add robots.txt
COPY robots.txt /opt/mafiascum/forum

ENTRYPOINT [ "/opt/mafiascum/scripts/entrypoint.sh" ]
CMD ["apache2-foreground"]