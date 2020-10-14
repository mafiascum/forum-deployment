FROM bitnami/phpbb:3.3.1

USER root

COPY /composer-extension-manifest.json /opt/bitnami/phpbb
COPY ./install_extensions.sh /opt/bitnami/phpbb

RUN chmod +x /opt/bitnami/phpbb/install_extensions.sh
RUN /opt/bitnami/phpbb/install_extensions.sh

ENV PHPBB_DATA_TO_PERSIST="store files images"