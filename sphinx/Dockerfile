FROM romeoz/docker-sphinxsearch

RUN apt-get update && apt-get install -y wget cron

ENV DOCKERIZE_VERSION v0.6.0
RUN wget https://github.com/jwilder/dockerize/releases/download/$DOCKERIZE_VERSION/dockerize-linux-amd64-$DOCKERIZE_VERSION.tar.gz \
    && tar -C /usr/local/bin -xzvf dockerize-linux-amd64-$DOCKERIZE_VERSION.tar.gz \
    && rm dockerize-linux-amd64-$DOCKERIZE_VERSION.tar.gz

ADD ./config/sphinx.conf /etc/sphinxsearch/sphinx.conf.template

COPY cron.d /etc/cron.d.template/
RUN chmod +x /etc/cron.d.template/*

# add scripts
RUN mkdir /opt/mafiascum
COPY scripts /opt/mafiascum/scripts
RUN chmod +x /opt/mafiascum/scripts/*

ENTRYPOINT ["dockerize", "-template", "/etc/sphinxsearch/sphinx.conf.template:/etc/sphinxsearch/sphinx.conf", "-template", "/etc/cron.d.template:/etc/cron.d", "-wait", "tcp://database:3306", "-timeout", "30s", "/opt/mafiascum/scripts/entrypoint.sh"]
