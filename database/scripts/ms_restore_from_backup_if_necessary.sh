#!/bin/bash

DB_LATEST_TAR_FILE_NAME="mafiascum.backup.$MAFIASCUM_ENVIRONMENT.db.latest.7z"
# check volumes, if volumes missing, grab from S3 and pump it in. otherwise, do nothing.
if [ ! -d /var/lib/mysql/mysql ] 
then
    echo "Did not find existing volume: restoring from S3 backup..."
    if [ -z "$AWS_ACCESS_KEY_ID" ]; then
        aws s3 --no-sign-request cp "s3://$AWS_BACKUP_BUCKET/db-backups/$DB_LATEST_TAR_FILE_NAME" "/tmp/$DB_LATEST_TAR_FILE_NAME"
    else
        aws s3 cp "s3://$AWS_BACKUP_BUCKET/db-backups/$DB_LATEST_TAR_FILE_NAME" "/tmp/$DB_LATEST_TAR_FILE_NAME"
    fi

    if [ $? -eq 0 ];
    then
        if [ -z "${MAFIASCUM_BACKUP_PASSWORD}" ]; then
            7z -mx=9 -o"/docker-entrypoint-initdb.d" e "/tmp/$DB_LATEST_TAR_FILE_NAME"
        else
            7z -mx=9 -p"${MAFIASCUM_BACKUP_PASSWORD}" -o"/docker-entrypoint-initdb.d" e "/tmp/$DB_LATEST_TAR_FILE_NAME"
        fi
        rm "/tmp/$DB_LATEST_TAR_FILE_NAME"
    else
        echo "No backup for this environment found. Proceeding with empty DB"
    fi
else
    echo "Found database volume at /var/lib/mysql; proceeding."
fi
