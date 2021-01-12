FROM mariadb:10.5

# install utilities
RUN apt-get update
RUN apt-get install -y p7zip-full awscli cron

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