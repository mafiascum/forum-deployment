#!/bin/bash
# If this migration has already been run, do not run again
# in this case, we check that the user_skype column has already been dropped
SQL_EXISTS="show columns from phpbb_users like 'user_skype'"

if [ ! "$(mysql -N -s -h"${PHPBB_DATABASE_HOST}" -u"${PHPBB_DATABASE_USER}" -p"${PHPBB_DATABASE_PASSWORD}" -e"$SQL_EXISTS" "${PHPBB_DATABASE_NAME}")" ]
then
    echo "After Migration does not need to run."
else
    echo "After Migration Running..."
    cat /opt/bitnami/phpbb/phpbb/db/migration/data/after.sql | mysql -h${PHPBB_DATABASE_HOST} -u${PHPBB_DATABASE_USER} -p${PHPBB_DATABASE_PASSWORD} ${PHPBB_DATABASE_NAME}
fi