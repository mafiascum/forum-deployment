#!/bin/bash

SOURCE_DIR="/opt/bitnami/apache/logs/access/raw/"
ZIP_FILE_PATTERN="access-file.log.*.zip"
PARSED_DIRECTORY="/opt/bitnami/apache/logs/access/formatted/"
TIMESTAMP_TODAY=`date '+%Y%m%d' -d "0 days ago"`
TIMESTAMP_YESTERDAY=`date '+%Y%m%d' -d "1 days ago"`
BUCKET_NAME="$AWS_BACKUP_BUCKET"
HOSTNAME="`hostname`"

mkdir -p "$PARSED_DIRECTORY"

for file in `find "$SOURCE_DIR" -maxdepth 1 -mindepth 1 -type f -name "$ZIP_FILE_PATTERN" | sort -n` ; do
    unzip -p "$file" '-' | sort -t' ' -k4,5 | /usr/local/bin/access_log_parser -fileNameFormat 'access-parsed.%s.log' -writePath "${PARSED_DIRECTORY}/"

    fileNameRenamed="${HOSTNAME}-${file##*/}"

    aws s3 cp "$file" "s3://$BUCKET_NAME/logs/access/original/new-format/$fileNameRenamed"

    rm -f "$file"
done

for file in `find "$PARSED_DIRECTORY" -maxdepth 1 -mindepth 1 -type f -name "access-parsed.*.log" -not -name "access-parsed.$TIMESTAMP_TODAY.log" -not -name "access-parsed.$TIMESTAMP_YESTERDAY.log"` ; do
    compressedFile="${file}.zip"
    compressedFileNameRenamed="${HOSTNAME}-${compressedFile##*/}"

    zip -j "$compressedFile" "$file"
    aws s3 cp "$compressedFile" "s3://$BUCKET_NAME/logs/access/formatted/$compressedFileNameRenamed"
    
    rm -f "$compressedFile"
    rm -f "$file"
done