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

    until [[ $(docker-compose exec web sh -c 'curl --max-time 2 -o /dev/null -s -w "%{http_code}\n" http://127.0.0.1:8080 | tr -d "\r"') == "200" ]]; do sleep 5; done

    docker-compose stop "$old_container_dc"
    docker-compose rm -f "$old_container_dc"
}

blue_ip=$(docker inspect -f '{{range.NetworkSettings.Networks}}{{.IPAddress}}{{end}}' forum-deployment_web_1)
blue_exists=$?
green_ip=$(docker inspect -f '{{range.NetworkSettings.Networks}}{{.IPAddress}}{{end}}' forum-deployment_web-alt_1)
green_exists=$?

if [[ blue_exists -eq 0 && green_exists -ne 0 ]]; then
    blue_response=$(docker-compose exec web sh -c 'curl --max-time 2 -o /dev/null -s -w "%{http_code}\n" http://127.0.0.1:8080 | tr -d "\r"')
    if [[ "$blue_response" == "200" ]]; then
        echo "Blue (main) container is serving connections."
        echo "Green (alternate) container coming up to replace it."
        swap_containers true

    else
        echo "No connectivity on blue (main) container. Not acting."
        exit 1
    fi
elif [[ blue_exists -ne 0 && green_exists -eq 0 ]]; then
    green_response=$(docker-compose exec web-alt sh -c 'curl --max-time 2 -o /dev/null -s -w "%{http_code}\n" http://127.0.0.1:8080 | tr -d "\r"')
    if [[ "$green_response" == "200" ]]; then
        echo "Green (alternate) container is serving connections."
        echo "Blue (main) container coming up to replace it."
        swap_containers false
    else
        echo "No connectivity on green (alternate) container. Not acting."
        exit 1
    fi
elif [[ blue_exists -eq 0 && green_exists -eq 0 ]]; then
    echo "Both containers currently up. Unsure what to do. Kill one of them to continue."
    exit 1
else
    echo "Neither container is up. Cannot do a zero downtime deploy when already down."
fi