#!/bin/bash

DATE=`TZ="America/New_York" date +%Y%m%d_%H%M%S`
DB_TAR_FILE_NAME="mafiascum.backup.$MAFIASCUM_ENVIRONMENT.db.$DATE.7z"
DB_LATEST_TAR_FILE_NAME="mafiascum.backup.$MAFIASCUM_ENVIRONMENT.db.latest.7z"

cd /tmp
mysqldump -u${PHPBB_DATABASE_USER} -p${PHPBB_DATABASE_PASSWORD} --lock-tables=false --routines --triggers --databases ms_phpbb3 ms_mediawiki > ms_phpbb3_and_ms_mediawiki.sql
7z -mx=9 -mmt2 -p${MAFIASCUM_BACKUP_PASSWORD} a "$DB_TAR_FILE_NAME" ms_phpbb3_and_ms_mediawiki.sql
rm -f ms_phpbb3_and_ms_mediawiki.sql
aws s3 cp "$DB_TAR_FILE_NAME" "s3://$AWS_BACKUP_BUCKET/db-backups/$DB_TAR_FILE_NAME"
aws s3 cp "s3://$AWS_BACKUP_BUCKET/db-backups/$DB_TAR_FILE_NAME" "s3://$AWS_BACKUP_BUCKET/db-backups/$DB_LATEST_TAR_FILE_NAME"
rm -f $DB_TAR_FILE_NAME