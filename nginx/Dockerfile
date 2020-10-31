FROM nginx

RUN apt-get update && apt-get install -y wget

ENV DOCKERIZE_VERSION v0.6.1
RUN wget https://github.com/jwilder/dockerize/releases/download/$DOCKERIZE_VERSION/dockerize-linux-amd64-$DOCKERIZE_VERSION.tar.gz \
    && tar -C /usr/local/bin -xzvf dockerize-linux-amd64-$DOCKERIZE_VERSION.tar.gz \
    && rm dockerize-linux-amd64-$DOCKERIZE_VERSION.tar.gz

ADD ./sites /etc/nginx/conf.d.templates
CMD dockerize -template /etc/nginx/conf.d.templates:/etc/nginx/conf.d -stdout /var/log/nginx/access.log -stderr /var/log/nginx/error.log -wait tcp://web:8080 -timeout 300s nginx -g 'daemon off;'
