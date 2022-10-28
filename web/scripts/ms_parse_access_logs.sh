#!/bin/bash

source /opt/bitnami/scripts/mafiascum/.env.sh

ZIP_FILE_PATTERN="$1"
FORMATTED_FILE_NAME_FORMAT="$2"

if [ -z "$1" || -z "$2" ] ; then
    echo "Usage: <<ZipFilePattern>> <<FormattedFileNamePattern>>"
    exit
fi

SOURCE_DIR="/opt/bitnami/apache/logs/access/raw/"
PARSED_DIRECTORY="/opt/bitnami/apache/logs/access/formatted/"
TIMESTAMP_TODAY=`date '+%Y%m%d' -d "0 days ago"`
TIMESTAMP_YESTERDAY=`date '+%Y%m%d' -d "1 days ago"`
BUCKET_NAME="$AWS_BACKUP_BUCKET"
HOSTNAME="`hostname`"

mkdir -p "$PARSED_DIRECTORY"

for file in `find "$SOURCE_DIR" -maxdepth 1 -mindepth 1 -type f -name "$ZIP_FILE_PATTERN" | sort -n` ; do
    unzip -p "$file" '-' | sort -t' ' -k4,5 | /usr/local/bin/access_log_parser -fileNameFormat "$FORMATTED_FILE_NAME_FORMAT" -writePath "${PARSED_DIRECTORY}/"

    fileNameRenamed="${HOSTNAME}-${file##*/}"

    aws s3 cp "$file" "s3://$BUCKET_NAME/logs/access/original/new-format/$fileNameRenamed"

    rm -f "$file"
done

## Replace the '%s' with '*' for the find command
FORMATTED_FILE_SEARCH_PATTERN=`echo "$FORMATTED_FILE_NAME_FORMAT" | sed 's/%s/*/g'`
FORMATTED_FILE_TODAY_PATTERN=`echo "$FORMATTED_FILE_NAME_FORMAT" | sed 's/%s/'"$TIMESTAMP_TODAY"'/g'`
FORMATTED_FILE_YESTERDAY_PATTERN=`echo "$FORMATTED_FILE_NAME_FORMAT" | sed 's/%s/'"$TIMESTAMP_YESTERDAY"'/g'`
for file in `find "$PARSED_DIRECTORY" -maxdepth 1 -mindepth 1 -type f -name "$FORMATTED_FILE_SEARCH_PATTERN" -not -name "$FORMATTED_FILE_TODAY_PATTERN" -not -name "$FORMATTED_FILE_YESTERDAY_PATTERN"` ; do
    compressedFile="${file}.zip"
    compressedFileNameRenamed="${HOSTNAME}-${compressedFile##*/}"

    zip -j "$compressedFile" "$file"
    aws s3 cp "$compressedFile" "s3://$BUCKET_NAME/logs/access/formatted/$compressedFileNameRenamed"
    
    rm -f "$compressedFile"
    rm -f "$file"
done