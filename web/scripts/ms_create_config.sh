[ "$MAFIASCUM_DEBUG" == 'true' ] && set -x

envsubst '${PHPBB_DATABASE_HOST}
${PHPBB_DATABASE_USER}
${PHPBB_DATABASE_PASSWORD}
${PHPBB_DATABASE_NAME}
${PHPBB_DATABASE_PORT_NUMBER}
${MAFIASCUM_SITE_CHAT_URL}' < "/opt/mafiascum/forum/config.php.tpl" > "/opt/mafiascum/forum/config.php"