FROM mariadb:10.5

# install utilities
RUN apt-get update
RUN apt-get install -y p7zip-full wget awscli cron

ENV DOCKERIZE_VERSION v0.6.0
RUN wget https://github.com/jwilder/dockerize/releases/download/$DOCKERIZE_VERSION/dockerize-linux-amd64-$DOCKERIZE_VERSION.tar.gz \
    && tar -C /usr/local/bin -xzvf dockerize-linux-amd64-$DOCKERIZE_VERSION.tar.gz \
    && rm dockerize-linux-amd64-$DOCKERIZE_VERSION.tar.gz

# add my.cnf
COPY my.cnf /etc/mysql/my.cnf.template

# add scripts
RUN mkdir /opt/mafiascum
COPY scripts /opt/mafiascum/scripts
RUN chmod +x /opt/mafiascum/scripts/*

# add cron
COPY cron.d /etc/cron.d/

ENTRYPOINT ["dockerize", "-template", "/etc/mysql/my.cnf.template:/etc/mysql/my.cnf", "/opt/mafiascum/scripts/entrypoint.sh"]
CMD ["mysqld"]