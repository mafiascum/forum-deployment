#!/bin/bash

# populate the volume if needed
/opt/mafiascum/scripts/ms_restore_from_backup_if_necessary.sh

# cron
service crond start

/usr/local/bin/docker-entrypoint.sh "$@"