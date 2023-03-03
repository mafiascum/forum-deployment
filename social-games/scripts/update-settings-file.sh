#!/bin/bash

WEB_NAME="$1"
SCHEMA_NAME="$2"
WEB_PREFIX="$3"
MYSQL_USER_FOR_BOARD="$4"
MYSQL_PASSWORD_FOR_BOARD="$5"

DEST_PATH="/var/www/html/$WEB_NAME"
BOARD_URL=$(echo "${SOCIAL_GAMES_HTTP_SCHEMA}${SOCIAL_GAMES_DOMAIN_NAME}${WEB_PREFIX}/${WEB_NAME}" | sed 's/\//\\\//g')
BOARD_DIR=$(echo "${DEST_PATH}" | sed 's/\//\\\//g')
SOURCE_DIR=$(echo "${DEST_PATH}/Sources" | sed 's/\//\\\//g')
CACHE_DIR=$(echo "${DEST_PATH}/cache" | sed 's/\//\\\//g')

sed -i -E "s/^\\\$db_server.*$/\\\$db_server = '$SOCIAL_GAMES_DB_HOST';/g ; s/^\\\$db_name.*$/\\\$db_name = '$SCHEMA_NAME';/g ; s/^\\\$db_user.*$/\\\$db_user = '$MYSQL_USER_FOR_BOARD';/g ; s/^\\\$db_passwd.*$/\\\$db_passwd = '$MYSQL_PASSWORD_FOR_BOARD';/g" "$DEST_PATH/Settings.php"
sed -i -E "s/^\\\$boardurl.*$/\\\$boardurl = '$BOARD_URL';/g ; s/^\\\$boarddir.*$/\\\$boarddir = '$BOARD_DIR';/g ; s/^\\\$sourcedir.*$/\\\$sourcedir = '$SOURCE_DIR';/g ; s/^\\\$cachedir.*$/\\\$cachedir = '$CACHE_DIR';/g" "$DEST_PATH/Settings.php"