#!/bin/bash

set -x

docker-compose exec nginx sh -c 'cp /etc/nginx/nginx-blue.conf /etc/nginx/nginx.conf'
docker-compose exec nginx sh -c "nginx -s reload"
