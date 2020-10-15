FROM bitnami/phpbb:3.3.1

USER root

ADD ./init.d/install_extensions.sh /docker-entrypoint-init.d/install_extensions.sh
RUN chmod +x /docker-entrypoint-init.d/install_extensions.sh

ENV PHPBB_DATA_TO_PERSIST="store files images config.php"