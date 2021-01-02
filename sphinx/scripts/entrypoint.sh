#!/bin/bash

service cron start
/sbin/entrypoint.sh "$@"

