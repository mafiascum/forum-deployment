#!/bin/bash

. /opt/bitnami/scripts/libfs.sh

FORUM_LATEST_TAR_FILE_NAME="mafiascum.backup.$MAFIASCUM_ENVIRONMENT.forum.latest.zip"
WIKI_LATEST_TAR_FILE_NAME="mafiascum.backup.$MAFIASCUM_ENVIRONMENT.wiki.latest.zip"

# check volumes, if volumes missing, grab from S3

if is_mounted_dir_empty "${BITNAMI_VOLUME_DIR}/phpbb" 
then
    echo "Did not find existing forum volume: restoring from S3 backup..."
    aws s3 cp "s3://$AWS_BACKUP_BUCKET/web-backups/$FORUM_LATEST_TAR_FILE_NAME" /tmp/$FORUM_LATEST_TAR_FILE_NAME
    unzip -P${MAFIASCUM_BACKUP_PASSWORD} -d "${BITNAMI_VOLUME_DIR}/phpbb" /tmp/$FORUM_LATEST_TAR_FILE_NAME
    rm /tmp/$FORUM_LATEST_TAR_FILE_NAME
else
    echo "Found existing forum volume; proceeding."
fi

if is_mounted_dir_empty "${BITNAMI_VOLUME_DIR}/wiki" 
then
    echo "Did not find existing wiki volume: restoring from S3 backup..."
    aws s3 cp "s3://$AWS_BACKUP_BUCKET/web-backups/$WIKI_LATEST_TAR_FILE_NAME" /tmp/$WIKI_LATEST_TAR_FILE_NAME
    unzip -P${MAFIASCUM_BACKUP_PASSWORD} -d "${BITNAMI_VOLUME_DIR}/wiki" /tmp/$WIKI_LATEST_TAR_FILE_NAME
    rm /tmp/$WIKI_LATEST_TAR_FILE_NAME
else
    echo "Found existing wiki volume; proceeding."
fi

