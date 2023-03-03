#!/bin/bash

printenv | perl -pe "s|(^.*?)=(.*$)|export \1\='\2'|" > /root/.env.sh
/scripts/load-games.py
service cron start

(
    env -i HOME="$HOME" /usr/sbin/apache2ctl -D FOREGROUND
)