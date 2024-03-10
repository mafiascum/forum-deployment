#!/bin/bash

[ "$MAFIASCUM_DEBUG" == 'true' ] && set -x

envsubst '${PHPBB_DATABASE_HOST}
${PHPBB_DATABASE_USER}
${PHPBB_DATABASE_PASSWORD}
${PHPBB_DATABASE_NAME}
${PHPBB_DATABASE_PORT_NUMBER}
${SPHINX_ID}
${SPHINX_HOST}' < /etc/sphinxsearch/sphinx.conf.template > /etc/sphinxsearch/sphinx.conf

if [[ $MAFIASCUM_ENVIRONMENT == 'development' ]] || [[ $MAFIASCUM_ENVIRONMENT == 'dev' ]] || [[ $MAFIASCUM_ENVIRONMENT == 'local' ]]; then
    indexer --config ${SPHINX_CONF} --all
fi

service cron start
for file in /etc/cron.d.template/*; do
    bfile=$(basename "$file")
    envsubst < "$file" > "/etc/cron.d/$bfile"
    chmod +x "/etc/cron.d/$bfile"
done

/sbin/entrypoint.sh "$@"

