FROM romeoz/docker-sphinxsearch

RUN apt-get update && apt-get install -y wget cron

ENV DOCKERIZE_VERSION v0.6.0
RUN wget https://github.com/jwilder/dockerize/releases/download/$DOCKERIZE_VERSION/dockerize-linux-amd64-$DOCKERIZE_VERSION.tar.gz \
    && tar -C /usr/local/bin -xzvf dockerize-linux-amd64-$DOCKERIZE_VERSION.tar.gz \
    && rm dockerize-linux-amd64-$DOCKERIZE_VERSION.tar.gz

ADD ./config/sphinx.conf /etc/sphinxsearch/sphinx.conf.template

ADD crontab /tmp/sphinx_cron
RUN chmod 0644 /tmp/sphinx_cron

ENTRYPOINT ["sh", "-c", "cron && dockerize -template /etc/sphinxsearch/sphinx.conf.template:/etc/sphinxsearch/sphinx.conf -template /tmp:/etc/cron.d  -wait tcp://$PHPBB_DATABASE_HOST:$PHPBB_DATABASE_PORT_NUMBER -timeout 30s /sbin/entrypoint.sh"]
