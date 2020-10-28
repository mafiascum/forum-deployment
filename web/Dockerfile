FROM bitnami/phpbb:3.3.1

USER root

RUN apt-get update
RUN apt-get install -y zip unzip awscli rsync jq

RUN mkdir /opt/bitnami/scripts/mafiascum

# Only persist things we don't plan to install ourselves
ENV PHPBB_DATA_TO_PERSIST="store files images"

# copy legacy sites
COPY www /opt/bitnami/www

# copy vhost templates
COPY vhost_templates/forum.conf.tpl /opt/bitnami/scripts/apache/bitnami-templates
COPY vhost_templates/www.conf.tpl /opt/bitnami/scripts/apache/bitnami-templates
COPY vhost_templates/wiki.conf.tpl /opt/bitnami/scripts/apache/bitnami-templates

# add scripts
COPY scripts /opt/bitnami/scripts/mafiascum
RUN chmod +x /opt/bitnami/scripts/mafiascum/*

# install wikimedia and extensions
RUN /opt/bitnami/scripts/mafiascum/ms_install_mediawiki.sh

COPY wiki/LocalSettings.php /opt/bitnami/wiki

# add substitute migrations
RUN mkdir /opt/bitnami/scripts/mafiascum/migration
COPY forum/migration /opt/bitnami/scripts/mafiascum/migration
RUN rsync -avz /opt/bitnami/scripts/mafiascum/migration/db/data/ /opt/bitnami/phpbb/phpbb/db/migration/data/

# add cron
COPY cron.d /etc/cron.d/
RUN chmod +x /etc/cron.d/mafiascum

# make log locations for directories
RUN /opt/bitnami/scripts/mafiascum/ms_logs.sh

# add docker config.php.tpl
COPY forum/config.php.tpl /opt/bitnami/phpbb

ENTRYPOINT [ "/opt/bitnami/scripts/mafiascum/entrypoint.sh" ]
CMD [ "/opt/bitnami/scripts/apache/run.sh" ]