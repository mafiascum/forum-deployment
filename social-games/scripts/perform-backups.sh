#!/bin/bash

source /root/.env.sh
cd /var/www/html
DATE=`TZ="America/New_York" date +%Y%m%d_%H%M%S`
BACKUP_LOCAL_DIR="/root"
WEB_BACKUPS_TO_RETAIN="3"
DB_BACKUPS_TO_RETAIN="3"

function prune_backups() {
    RECORDS_TO_RETAIN="$1"
    S3_DIRECTORY="$2"
    EXCLUSION_PATTERN="$3"
    TAIL_LINES=`expr "$RECORDS_TO_RETAIN" + 1`

    FILENAMES_TO_DELETE=`aws s3 ls "${S3_DIRECTORY}/" | grep -v "${EXCLUSION_PATTERN}" | sort -t' ' -k1,2 -nr | tail "--lines=+${TAIL_LINES}" | awk '{print $NF}'`

    while IFS= read -r FILE_TO_DELETE; do
        if [[ ! -z "$FILE_TO_DELETE" ]]
        then
            S3_PATH_TO_DELETE="${S3_DIRECTORY}/$FILE_TO_DELETE"
            echo "Deleting backup record: $S3_PATH_TO_DELETE"
            aws s3 rm "$S3_PATH_TO_DELETE"
        fi
    done <<< "$FILENAMES_TO_DELETE"
}

while IFS="" read -r recordLine || [ -n "$recordLine" ]
do
    WEB_NAME=`echo "$recordLine" | awk -F"\t" '{print $1}'`
    DB_NAME=`echo "$recordLine" | awk -F"\t" '{print $2}'`

    DB_S3_DIRECTORY="s3://${AWS_BACKUP_BUCKET}/social-games/db/${DB_NAME}"
    DB_DUMP_FILE_NAME="${DB_NAME}.sql"
    DB_DUMP_LOCAL_FILE_PATH="${BACKUP_LOCAL_DIR}/${DB_DUMP_FILE_NAME}"
    DB_ZIP_FILE_NAME="${DB_NAME}-${DATE}.sql.7z"
    DB_ZIP_LATEST_FILE_NAME="${DB_NAME}-latest.sql.7z"
    DB_ZIP_LOCAL_FILE_PATH="${BACKUP_LOCAL_DIR}/${DB_ZIP_FILE_NAME}"

    mysqldump --user="${SOCIAL_GAMES_DB_USER}" --password="${MYSQL_ROOT_PASSWORD}" --host="${SOCIAL_GAMES_DB_HOST}" --databases "${DB_NAME}" > "$DB_DUMP_LOCAL_FILE_PATH"
    7z -mx=1 -mmt2 -p${MAFIASCUM_BACKUP_PASSWORD} a "$DB_ZIP_LOCAL_FILE_PATH" "$DB_DUMP_LOCAL_FILE_PATH"
    aws s3 cp "${DB_ZIP_LOCAL_FILE_PATH}" "${DB_S3_DIRECTORY}/${DB_ZIP_FILE_NAME}"
    aws s3 cp "${DB_S3_DIRECTORY}/${DB_ZIP_FILE_NAME}" "${DB_S3_DIRECTORY}/${DB_ZIP_LATEST_FILE_NAME}"

    prune_backups "$DB_BACKUPS_TO_RETAIN" "$DB_S3_DIRECTORY" "\-latest\.sql\.7z"

    rm -f "$DB_DUMP_LOCAL_FILE_PATH"
    rm -f "$DB_ZIP_LOCAL_FILE_PATH"

    WEB_S3_DIRECTORY="s3://${AWS_BACKUP_BUCKET}/social-games/web/${WEB_NAME}"
    WEB_BACKUP_FILE_NAME="${WEB_NAME}-${DATE}.zip"
    WEB_BACKUP_LATEST_FILE_NAME="${WEB_NAME}-latest.zip"
    WEB_BACKUP_LOCAL_PATH="${BACKUP_LOCAL_DIR}/${WEB_BACKUP_FILE_NAME}"

    zip -qr -P"${MAFIASCUM_BACKUP_PASSWORD}" "${WEB_BACKUP_LOCAL_PATH}" "${WEB_NAME}"
    aws s3 cp "${WEB_BACKUP_LOCAL_PATH}" "${WEB_S3_DIRECTORY}/${WEB_BACKUP_FILE_NAME}"
    aws s3 cp "${WEB_S3_DIRECTORY}/${WEB_BACKUP_FILE_NAME}" "${WEB_S3_DIRECTORY}/${WEB_BACKUP_LATEST_FILE_NAME}"

    prune_backups "$WEB_BACKUPS_TO_RETAIN" "$WEB_S3_DIRECTORY" "\-latest\.zip"

    rm -f "${WEB_BACKUP_LOCAL_PATH}"
done < /game-index.txt