FROM php:7.3-apache

ARG DEBIAN_FRONTEND=noninteractive

RUN apt update -y
RUN apt install -y wget python3 pip nano p7zip-full mariadb-client-10.5 libpng-dev zip unzip awscli cron
RUN docker-php-ext-install mysqli
RUN a2enmod rewrite proxy
RUN pip3 install boto3 pymysql
RUN mkdir /snapshot-restore

COPY ./cron.d /etc/cron.d
COPY ./scripts/ /scripts/
COPY ./game-index.txt /
RUN echo "alias ll='ls -lA'" >> /etc/bash.bashrc

CMD ["/scripts/docker-entrypoint.sh"]