#!/bin/bash

set -x

swap_containers() {
    is_to_alternate=$1
    set -e

    if [[ $is_to_alternate == true ]]; then
        new_container_dc="web-alt"
        old_container_dc="web"
        new_container_name="forum-deployment_web-alt_1"
    else
        new_container_dc="web"
        old_container_dc="web-alt"
        new_container_name="forum-deployment_web_1"
    fi

    git checkout main
    git pull
    docker-compose build "$new_container_dc"
    docker-compose up -d "$new_container_dc"
    new_container_ip=$(docker inspect -f '{{range.NetworkSettings.Networks}}{{.IPAddress}}{{end}}' $new_container_name)

    until [[ $(docker-compose exec $new_container_dc sh -c 'curl --max-time 2 -o /dev/null -s -w "%{http_code}\n" http://127.0.0.1:8080' | tr -d '\r') == "200" ]]; do sleep 5; done

    docker-compose stop "$old_container_dc"
    docker-compose rm -f "$old_container_dc"

    set +e
}

toggle_upstreams() {
    is_to_alternate=$1
    set -e

    if [[ $is_to_alternate == true ]]; then
        docker-compose exec nginx sh -c 'cp /etc/nginx/nginx-green.conf /etc/nginx/nginx.conf'
    else
        docker-compose exec nginx sh -c 'cp /etc/nginx/nginx-blue.conf /etc/nginx/nginx.conf'
    fi

    docker-compose exec nginx sh -c "nginx -s reload"

    set +e
}

blue_response=$(docker-compose exec web sh -c 'curl --max-time 2 -o /dev/null -s -w "%{http_code}\n" http://127.0.0.1:8080' | tr -d '\r')
green_response=$(docker-compose exec web-alt sh -c 'curl --max-time 2 -o /dev/null -s -w "%{http_code}\n" http://127.0.0.1:8080' | tr -d '\r')

if [[ "$blue_response" == "200" && "$green_response" != "200" ]]; then
    echo "Blue (main) container is serving connections."
    echo "Green (alternate) container coming up to replace it."
    swap_containers true
    toggle_upstreams true
elif [[ "$blue_response" != "200" && "$green_response" == "200" ]]; then
    echo "Green (alternate) container is serving connections."
    echo "Blue (main) container coming up to replace it."
    swap_containers false
    toggle_upstreams false
elif [[ "$blue_response" == "200" && "$green_response" == "200" ]]; then
    echo "Both containers currently up. Unsure what to do. Kill one of them to continue."
    exit 1
else
    echo "Neither container is up. Cannot do a zero downtime deploy when already down. Start a web container."
fi