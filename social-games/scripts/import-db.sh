#!/bin/bash

DB_NAME="$1"

7z x -o"/snapshot-restore/" -p"${MAFIASCUM_BACKUP_PASSWORD}" "/snapshot-restore/${DB_NAME}.sql.7z"
mysql -u"${SOCIAL_GAMES_DB_USER}" --host="${SOCIAL_GAMES_DB_HOST}" --password="${MYSQL_ROOT_PASSWORD}" < "/snapshot-restore/${DB_NAME}.sql"
rm -f "/snapshot-restore/${DB_NAME}.sql"