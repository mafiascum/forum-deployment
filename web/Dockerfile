FROM php:8.1.14-apache

ARG PHP_INI_DEVELOPMENT
ENV PHP_INI_DEVELOPMENT=$PHP_INI_DEVELOPMENT

RUN apt-get update
RUN apt-get -y upgrade
RUN apt-get install -y zip unzip awscli rsync jq cron apache2-dev libmariadb3 libmariadb-dev mariadb-client python3-pip gcc g++ cmake logrotate nano libicu-dev
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli
RUN docker-php-ext-install intl && docker-php-ext-enable intl

RUN mkdir -p /opt/mafiascum/scripts

# Change this to change the version
ENV PHPBB_MAJOR_VERSION=3
ENV PHPBB_MINOR_VERSION=3
ENV PHPBB_PATCH_VERSION=10

# copy legacy sites
COPY www /opt/mafiascum/www

# copy vhost templates
COPY vhost_templates /opt/mafiascum/apache/vhost-templates

# add bash alias for all users
RUN echo "alias ll='ls -lA'" >> /etc/bash.bashrc

# logrotate
COPY logrotate.d/apache2 /etc/logrotate.d/apache2

# python 3 environment
RUN pip3 install mariadb

# add scripts
COPY scripts /opt/mafiascum/scripts
RUN chmod +x /opt/mafiascum/scripts/*

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

ENTRYPOINT [ "/opt/mafiascum/scripts/entrypoint.sh" ]
CMD ["apache2-foreground"]