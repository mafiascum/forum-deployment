FROM mariadb:11.7

ARG TARGETPLATFORM

# install utilities
RUN apt-get update
RUN apt-get install -y p7zip-full wget cron unzip
RUN echo "$TARGETPLATFORM"
RUN if [ "$TARGETPLATFORM" = "linux/arm64" ]; then wget "https://awscli.amazonaws.com/awscli-exe-linux-aarch64.zip" -O "awscliv2.zip"; else wget "https://awscli.amazonaws.com/awscli-exe-linux-x86_64.zip" -O "awscliv2.zip"; fi
RUN unzip awscliv2.zip
RUN ./aws/install

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
CMD ["mariadbd"]