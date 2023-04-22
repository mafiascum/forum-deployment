#!/bin/bash

set -x

docker-compose exec nginx sh -c 'cp /etc/nginx/nginx-green.conf /etc/nginx/nginx.conf'
docker-compose exec nginx sh -c "nginx -s reload"
