#!/bin/bash

[ "$MAFIASCUM_DEBUG" == 'true' ] && set -x

. /opt/mafiascum/scripts/ms_lib_utils.sh

env

FORUM_LATEST_TAR_FILE_NAME="mafiascum.backup.$MAFIASCUM_ENVIRONMENT.forum.latest.zip"
WIKI_LATEST_TAR_FILE_NAME="mafiascum.backup.$MAFIASCUM_ENVIRONMENT.wiki.latest.zip"

# check volumes, if volumes missing, grab from S3

if is_mounted_dir_empty "/data/forum" 
then
    echo "Did not find existing forum volume: restoring from S3 backup..."
    if [ -z "$AWS_ACCESS_KEY_ID" ]; then
        aws s3 --no-sign-request cp "s3://$AWS_BACKUP_BUCKET/web-backups/$FORUM_LATEST_TAR_FILE_NAME" /tmp/$FORUM_LATEST_TAR_FILE_NAME
    else
        aws s3 cp "s3://$AWS_BACKUP_BUCKET/web-backups/$FORUM_LATEST_TAR_FILE_NAME" /tmp/$FORUM_LATEST_TAR_FILE_NAME
    fi
    if [ -z "${MAFIASCUM_BACKUP_PASSWORD}" ]; then
        unzip -d "/data/forum" /tmp/$FORUM_LATEST_TAR_FILE_NAME
    else
        unzip -P${MAFIASCUM_BACKUP_PASSWORD} -d "/data/forum" /tmp/$FORUM_LATEST_TAR_FILE_NAME
    fi
    rm /tmp/$FORUM_LATEST_TAR_FILE_NAME
    chown -R daemon:root "/data/forum"
else
    echo "Found existing forum volume; proceeding."
fi

if is_mounted_dir_empty "/data/wiki" 
then
    echo "Did not find existing wiki volume: restoring from S3 backup..."
    if [ -z "$AWS_ACCESS_KEY_ID" ]; then
        aws s3 --no-sign-request cp "s3://$AWS_BACKUP_BUCKET/web-backups/$WIKI_LATEST_TAR_FILE_NAME" /tmp/$WIKI_LATEST_TAR_FILE_NAME
    else
        aws s3 cp "s3://$AWS_BACKUP_BUCKET/web-backups/$WIKI_LATEST_TAR_FILE_NAME" /tmp/$WIKI_LATEST_TAR_FILE_NAME
    fi
    if [ $? -eq 0 ]; then
        if [ -z "${MAFIASCUM_BACKUP_PASSWORD}" ]; then
            unzip -d "/data/wiki" /tmp/$WIKI_LATEST_TAR_FILE_NAME
        else
            unzip -P${MAFIASCUM_BACKUP_PASSWORD} -d "/data/wiki" /tmp/$WIKI_LATEST_TAR_FILE_NAME
        fi
        rm /tmp/$WIKI_LATEST_TAR_FILE_NAME
    else
        echo "No wiki backup found. Creating empty wiki data directories if needed."
        mkdir -p /data/wiki/images
    fi
    chown -R daemon:root "/data/wiki"
else
    echo "Found existing wiki volume; proceeding."
fi

