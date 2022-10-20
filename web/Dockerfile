FROM bitnami/phpbb:3.3.8

USER root

RUN apt-get update
RUN apt-get install -y zip unzip awscli rsync jq cron apache2-dev libmariadb3 libmariadb-dev python3-pip gcc g++ cmake logrotate

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

# logrotate
COPY logrotate.d/apache2 /etc/logrotate.d/apache2

# python 3 environment
RUN pip3 install mariadb

# install pgn4web for chess tags
RUN /opt/bitnami/scripts/mafiascum/ms_install_pgn4web.sh

# install access log parser
RUN /opt/bitnami/scripts/mafiascum/ms_install_access_parser.sh

# install wikimedia and extensions
RUN /opt/bitnami/scripts/mafiascum/ms_install_mediawiki.sh

COPY wiki/LocalSettings.php /opt/bitnami/wiki

# add substitute migrations
RUN mkdir /opt/bitnami/scripts/mafiascum/migration
COPY forum/migration /opt/bitnami/scripts/mafiascum/migration
RUN rsync -avz /opt/bitnami/scripts/mafiascum/migration/db/data/ /opt/bitnami/phpbb/phpbb/db/migration/data/

# monkey patched files. this is your LAST RESORT for doing custom script work - try to use extensions if at all possible.
RUN mkdir /opt/bitnami/scripts/mafiascum/patches
COPY forum/patches /opt/bitnami/scripts/mafiascum/patches
RUN rsync -avz /opt/bitnami/scripts/mafiascum/patches/ /opt/bitnami/phpbb/

# add cron
COPY cron.d /etc/cron.d/

# make log locations for directories
RUN /opt/bitnami/scripts/mafiascum/ms_logs.sh

# add docker config.php.tpl
COPY forum/config.php.tpl /opt/bitnami/phpbb

ENTRYPOINT [ "/opt/bitnami/scripts/mafiascum/entrypoint.sh" ]
CMD [ "/opt/bitnami/scripts/apache/run.sh" ]