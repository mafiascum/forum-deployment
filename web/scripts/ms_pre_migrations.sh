#!/bin/bash
# If this migration has already been run, do not run again
SQL_EXISTS="show columns from phpbb_posts like 'post_visibility'"

if [ $(mysql -N -s -h"${PHPBB_DATABASE_HOST}" -u"${PHPBB_DATABASE_USER}" -p"${PHPBB_DATABASE_PASSWORD}" -e"$SQL_EXISTS" "${PHPBB_DATABASE_NAME}") ]
then
    echo "Before Migration does not need to run."
else
    echo "Before Migration Running..."
    cat /opt/bitnami/phpbb/phpbb/db/migration/data/before.sql | mysql -h${PHPBB_DATABASE_HOST} -u${PHPBB_DATABASE_USER} -p${PHPBB_DATABASE_PASSWORD} ${PHPBB_DATABASE_NAME}
fi