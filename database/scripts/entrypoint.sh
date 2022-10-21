#!/bin/bash

# Import envionment and start cron
printenv | perl -pe "s|(^.*?)=(.*$)|export \1\='\2'|" > /opt/mafiascum/.env.sh

chmod +x /opt/mafiascum/.env.sh
service cron start

# populate the volume if needed
#/opt/mafiascum/scripts/ms_restore_from_backup_if_necessary.sh

/usr/local/bin/docker-entrypoint.sh "$@"