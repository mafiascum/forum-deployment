#!/bin/bash

WEB_NAME="$1"

cd /snapshot-restore/
aws s3 cp s3://"${AWS_BACKUP_BUCKET}/social-games/web/${WEB_NAME}/${WEB_NAME}-latest.zip" /snapshot-restore/
unzip -P"${MAFIASCUM_BACKUP_PASSWORD}" "/snapshot-restore/${WEB_NAME}-latest.zip"

chown -R www-data:www-data ${WEB_NAME}
mv "/snapshot-restore/${WEB_NAME}" "${SOCIAL_GAMES_DOC_ROOT}/"

rm -f "/snapshot-restore/${WEB_NAME}-latest.zip"