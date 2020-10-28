#!/bin/bash
DATE=`TZ="America/New_York" date +%Y%m%d_%H%M%S`
FORUM_TAR_FILE_NAME="mafiascum.backup.$MAFIASCUM_ENVIRONMENT.forum.$DATE.zip"
FORUM_LATEST_TAR_FILE_NAME="mafiascum.backup.$MAFIASCUM_ENVIRONMENT.forum.latest.zip"
WIKI_TAR_FILE_NAME="mafiascum.backup.$MAFIASCUM_ENVIRONMENT.wiki.$DATE.zip"
WIKI_LATEST_TAR_FILE_NAME="mafiascum.backup.$MAFIASCUM_ENVIRONMENT.wiki.latest.zip"

echo "Backing up forum data"
cd /bitnami/phpbb
zip -r -P${MAFIASCUM_BACKUP_PASSWORD} "$FORUM_TAR_FILE_NAME" *
echo "Sending forum data to S3"
aws s3 cp "$FORUM_TAR_FILE_NAME" "s3://$AWS_BACKUP_BUCKET/web-backups/$FORUM_TAR_FILE_NAME"
aws s3 cp "s3://$AWS_BACKUP_BUCKET/web-backups/$FORUM_TAR_FILE_NAME" "s3://$AWS_BACKUP_BUCKET/web-backups/$FORUM_LATEST_TAR_FILE_NAME"
rm "$FORUM_TAR_FILE_NAME"

echo "Backing up wiki data"
cd /bitnami/wiki
zip -r -P${MAFIASCUM_BACKUP_PASSWORD} "$WIKI_TAR_FILE_NAME" *
echo "Sending wiki data to S3"
aws s3 cp "$WIKI_TAR_FILE_NAME" "s3://$AWS_BACKUP_BUCKET/web-backups/$WIKI_TAR_FILE_NAME"
aws s3 cp "s3://$AWS_BACKUP_BUCKET/web-backups/$WIKI_TAR_FILE_NAME" "s3://$AWS_BACKUP_BUCKET/web-backups/$WIKI_LATEST_TAR_FILE_NAME"
rm "$WIKI_TAR_FILE_NAME"
